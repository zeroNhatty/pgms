import VectorLayer from "ol/layer/Vector.js";
import VectorSource from "ol/source/Vector.js";

import CircleStyle from "ol/style/Circle.js";
import Fill from "ol/style/Fill.js";
import Stroke from "ol/style/Stroke.js";
import Style from "ol/style/Style.js";

const nodeStyle = new Style({
  image: new CircleStyle({
    radius: 8,

    fill: new Fill({
      color: "#ef4444",
    }),

    stroke: new Stroke({
      color: "#ffffff",
      width: 3,
    }),
  }),
});

export function createNodeLayer(
  source: VectorSource,
) {
  return new VectorLayer({
    source,
    style: nodeStyle,
  });
}
