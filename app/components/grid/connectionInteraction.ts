import PointerInteraction from "ol/interaction/Pointer.js";
import Feature from "ol/Feature.js";

import Point from "ol/geom/Point.js";
import LineString from "ol/geom/LineString.js";

import VectorLayer from "ol/layer/Vector.js";
import VectorSource from "ol/source/Vector.js";

export function createConnectionInteraction(
  nodeLayer: VectorLayer<VectorSource>,
  connectionSource: VectorSource,
) {
  let startNode: Feature | null = null;
  let previewLine: Feature<LineString> | null = null;

  const interaction = new PointerInteraction({
    handleDownEvent: ( event ) => {
      const node = event.map.forEachFeatureAtPixel(
        event.pixel,
        (feature) => feature,
        {
          layerFilter: (layer) =>
            layer === nodeLayer,
        },
      );

      if (!node) {
        return false;
      }

      startNode = node as Feature;

      return true;
    },

    handleDragEvent: (event) => {
      if (!startNode) {
        return;
      }

      const startGeometry =
        startNode.getGeometry() as Point;

      const startCoordinate =
        startGeometry.getCoordinates();

      const currentCoordinate =
        event.coordinate;

      if (!previewLine) {
        previewLine = new Feature({
          geometry: new LineString([
            startCoordinate,
            currentCoordinate,
          ]),
        });

        connectionSource.addFeature( previewLine );
      } else {
        previewLine.getGeometry()?.setCoordinates([
            startCoordinate,
            currentCoordinate,
          ]);
      }
    },

    handleUpEvent: (event) => {
      if (!startNode) {
        return false;
      }

      const endNode =
        event.map.forEachFeatureAtPixel(
          event.pixel,
          (feature) => feature,
          {
            layerFilter: (layer) =>
              layer === nodeLayer,
          },
        );

      if ( endNode && endNode !== startNode ) {
        const startGeometry =
          startNode.getGeometry() as Point;

        const endGeometry =
          (endNode as Feature).getGeometry() as Point;

        const connection =
          new Feature({
            geometry: new LineString([
              startGeometry.getCoordinates(),
              endGeometry.getCoordinates(),
            ]),
          });

        connection.set(
          "id",
          crypto.randomUUID(),
        );

        connection.set(
          "from",
          startNode.get("id"),
        );

        connection.set(
          "to",
          (endNode as Feature).get("id"),
        );

        connectionSource.addFeature(
          connection,
        );

        console.log(
          "Connection created:",
          {
            id: connection.get("id"),
            from: startNode.get("id"),
            to: (endNode as Feature).get(
              "id",
            ),
          },
        );
      }

      // Remove temporary preview line
      if (previewLine) {
        connectionSource.removeFeature(
          previewLine,
        );

        previewLine = null;
      }

      startNode = null;

      return false;
    },
  });

  return interaction;
}
