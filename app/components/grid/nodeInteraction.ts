import Draw from "ol/interaction/Draw.js";
import VectorSource from "ol/source/Vector.js";
import Point from "ol/geom/Point.js";
import { toLonLat } from "ol/proj.js";

export function createNodeInteraction(
  nodeSource: VectorSource,
  clipSource: VectorSource,
) {
  const draw = new Draw({
    source: nodeSource,
    type: "Point",

    condition: (event) => {
      let insideAddis = false;

      clipSource.forEachFeature((feature) => {
        const geometry = feature.getGeometry();

        if (
          geometry?.intersectsCoordinate(
            event.coordinate,
          )
        ) {
          insideAddis = true;
        }
      });

      return insideAddis;
    },
  });

  draw.on("drawend", (event) => {
    const feature = event.feature;

    const geometry =
      feature.getGeometry() as Point;

    const coordinate = geometry.getCoordinates();

    const [longitude, latitude] =
      toLonLat(coordinate);

    feature.set("id", crypto.randomUUID());
    feature.set("type", "substation");
    feature.set("longitude", longitude);
    feature.set("latitude", latitude);

    console.log("Node created:", {
      id: feature.get("id"),
      longitude,
      latitude,
    });
  });

  return draw;
}
