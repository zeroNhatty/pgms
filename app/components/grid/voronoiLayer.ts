import VectorLayer from "ol/layer/Vector.js";
import VectorSource from "ol/source/Vector.js";
import { Style, Stroke, Fill } from "ol/style.js";
import GeoJSON from "ol/format/GeoJSON.js";
import Point from "ol/geom/Point.js";
import { toLonLat, transformExtent } from "ol/proj.js";
import voronoi from "@turf/voronoi";
import { featureCollection, point } from "@turf/helpers";
import type { FeatureLike } from "ol/Feature.js";

const DEFAULT_STYLE = new Style({
  stroke: new Stroke({
    color: "rgba(34, 197, 94, 0.85)", // Green
    width: 1.5,
  }),
  fill: new Fill({
    color: "rgba(34, 197, 94, 0.15)",
  }),
});

const VORONOI_STATUS_STYLES: Record<string, Style> = {
  active: DEFAULT_STYLE,

  being_maintained: new Style({
    stroke: new Stroke({
      color: "rgba(234, 179, 8, 0.9)", // Yellow
      width: 1.5,
      lineDash: [6, 4],
    }),
    fill: new Fill({
      color: "rgba(234, 179, 8, 0.22)",
    }),
  }),

  inactive: new Style({
    stroke: new Stroke({
      color: "rgba(239, 68, 68, 0.9)", // Red
      width: 2,
    }),
    fill: new Fill({
      color: "rgba(239, 68, 68, 0.28)",
    }),
  }),
};

function voronoiStyleFunction(feature: FeatureLike): Style {
  const status = (feature.get("status") as string) || "active";
  return VORONOI_STATUS_STYLES[status] ?? DEFAULT_STYLE;
}

export function createVoronoiLayer(): {
  layer: VectorLayer<VectorSource>;
  source: VectorSource;
} {
  const source = new VectorSource();

  const layer = new VectorLayer({
    source,
    visible: false,
    style: voronoiStyleFunction,
    zIndex: 1,
  });

  return { layer, source };
}

/**
 * Recomputes Voronoi cells using current nodes
 */
export function updateVoronoiDiagram(
  nodeSource: VectorSource,
  voronoiSource: VectorSource,
  clipExtent3857?: [number, number, number, number]
) {
  voronoiSource.clear();

  const nodeFeatures = nodeSource.getFeatures();


  if (nodeFeatures.length < 3) {
    console.warn(
      `[Voronoi] Needs at least 3 nodes to draw cells. Current nodes: ${nodeFeatures.length}`
    );
    return;
  }

  const turfPoints = nodeFeatures
    .map((feat) => {
      const geom = feat.getGeometry();
      if (!(geom instanceof Point)) return null;

      const coords = toLonLat(geom.getCoordinates()) as [number, number];
      const status = (feat.get("status") as string) || "active";
      const id = String(feat.get("id") || "");

      return point(coords, { id, status });
    })
    .filter(Boolean) as ReturnType<typeof point>[];

  const pointsCollection = featureCollection(turfPoints);

  //fallback to addis
  let bbox4326: [number, number, number, number] = [
    38.6393, 8.8488, 38.9062, 9.1128,
  ];

  if (clipExtent3857 && isFinite(clipExtent3857[0])) {
    bbox4326 = transformExtent(
      clipExtent3857,
      "EPSG:3857",
      "EPSG:4326"
    ) as [number, number, number, number];
  }

  try {
    const voronoiPolygons = voronoi(pointsCollection, { bbox: bbox4326 });

    if (voronoiPolygons && voronoiPolygons.features.length > 0) {
      const olFeatures = new GeoJSON().readFeatures(voronoiPolygons, {
        featureProjection: "EPSG:3857",
        dataProjection: "EPSG:4326",
      });

      olFeatures.forEach((olFeat, idx) => {
        const matchingPoint = turfPoints[idx];
        const status = matchingPoint?.properties?.status ?? "active";
        const id = matchingPoint?.properties?.id ?? "";

        olFeat.set("status", status);
        olFeat.set("id", id);
      });

      voronoiSource.addFeatures(olFeatures);
      console.log(`[Voronoi] Generated ${olFeatures.length} cells`);
    }
  } catch (err) {
    console.error("[Voronoi] Calculation error:", err);
  }
}
