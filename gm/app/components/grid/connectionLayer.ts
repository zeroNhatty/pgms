import VectorLayer from "ol/layer/Vector.js";
import VectorSource from "ol/source/Vector.js";

import Stroke from "ol/style/Stroke.js";
import Style from "ol/style/Style.js";

const connectionStyle = new Style({
  stroke: new Stroke({
    color: "#2563eb",
    width: 3,
  }),
});

export function createConnectionLayer(
  source: VectorSource,
) {
  return new VectorLayer({
    source,
    style: connectionStyle,
  });
}
