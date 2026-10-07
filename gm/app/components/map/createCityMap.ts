import Map from "ol/Map.js";
import View from "ol/View.js";

import GeoJSON from "ol/format/GeoJSON.js";

import TileLayer from "ol/layer/Tile.js";
import VectorLayer from "ol/layer/Vector.js";

import OSM from "ol/source/OSM.js";
import VectorSource from "ol/source/Vector.js";

import Fill from "ol/style/Fill.js";
import Style from "ol/style/Style.js";

import { fromLonLat } from "ol/proj.js";
import { getVectorContext } from "ol/render.js";

import { defaults as defaultInteractions } from "ol/interaction/defaults.js";

import { createNodeLayer } from "../grid/nodeLayer";
import { createConnectionLayer } from "../grid/connectionLayer";
import { createVoronoiLayer } from "../grid/voronoiLayer";

export function createCityMap( target: string ) {
  const base = new TileLayer({
    source: new OSM(),
  });

  const clipSource =
    new VectorSource({
      url: "/addis-ababa.geojson",
      format: new GeoJSON(),
    });

  const clipLayer =
    new VectorLayer({
      source: clipSource,
      style: null,
    });

  const nodeSource = new VectorSource();

  const connectionSource = new VectorSource();

  const nodeLayer = createNodeLayer(nodeSource);

  const connectionLayer = createConnectionLayer(
      connectionSource,
    );
  const { layer: voronoiLayer, source: voronoiSource } = createVoronoiLayer();

  const clipStyle = new Style({
    fill: new Fill({
      color: "black",
    }),
  });

  clipSource.on( "addfeature", () => {
      const extent = clipSource.getExtent();

      if (extent != null) {
        base.setExtent(extent);

        console.log("ADDIS LOADED");
        console.log("Addis extent:", extent);
      }
    },
  );

  base.on( "postrender", (event) => {
      const vectorContext = getVectorContext(event);

      const context = event.context as CanvasRenderingContext2D;

      context.globalCompositeOperation = "destination-in";

      clipSource.forEachFeature((feature) => {
          vectorContext.drawFeature(
            feature,
            clipStyle,
          );
        },
      );

      context.globalCompositeOperation = "source-over";
    },
  );

  const map = new Map({
    target,

    layers: [
      base,
      clipLayer,
      voronoiLayer,
      connectionLayer,
      nodeLayer,
    ],

    interactions:
      defaultInteractions({
        dragPan: false,
      }),

    view: new View({
      center: fromLonLat([
        38.78,
        8.9571,
      ]),

      zoom: 11.7,
      minZoom: 11.7,
    }),
  });

  return {
    map,
    clipSource,
    nodeSource,
    connectionSource,
    nodeLayer,
    voronoiLayer,
    voronoiSource
  };
}
