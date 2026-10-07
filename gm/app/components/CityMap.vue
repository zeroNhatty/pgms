<script setup lang="ts">
import "ol/ol.css";
import Interaction from "ol/interaction/Interaction.js";

import { DrawMode } from "./DrawMode";

import { createCityMap } from "./map/createCityMap";

import { updateVoronoiDiagram } from "./grid/voronoiLayer";
import { createNodeInteraction } from "./grid/nodeInteraction";
import {createConnectionInteraction } from "./grid/connectionInteraction";
import { Point } from "ol/geom";
import { toLonLat, fromLonLat } from "ol/proj";
import Feature from "ol/Feature.js";


const client = useSanctumClient();

const props = defineProps<{
  setup: boolean;
  mode: DrawMode;
}>();

const emit = defineEmits<{
  save: [
    data: {
      nodes: {
        clientId: string;
        longitude: number;
        latitude: number;
      }[];
      relations: {
        from: string;
        to: string;
      }[];
    }
  ];
}>();

let mapData: ReturnType<typeof createCityMap> | null = null;

function refreshVoronoi() {
  if (!mapData) return;
  const extent = mapData.clipSource.getExtent() as [number, number, number, number];
  updateVoronoiDiagram(mapData.nodeSource, mapData.voronoiSource, extent);
}

async function loadNodes() {
  if (!mapData) return;

  try {
    const data = await client<any[]>("/api/nodes");

    if (Array.isArray(data) && data.length > 0) {
      mapData.nodeSource.clear();

      data.forEach((node) => {
        const feature = new Feature({
          geometry: new Point(fromLonLat([Number(node.longitude), Number(node.latitude)])),
        });

        feature.set("id", String(node.id));
        feature.set("status", node.status || "active");

        mapData!.nodeSource.addFeature(feature);
      });

      console.log(`Loaded ${data.length} nodes from backend`);

      // If currently in Default mode, generate Voronoi cells right away
      if (props.mode === DrawMode.Default) {
        refreshVoronoi();
      }
    }
  } catch (err) {
    console.error("Failed to load nodes from backend:", err);
  }
}

function saveMap() {
  if (!mapData) return;

  const nodes = mapData.nodeSource.getFeatures().map((feature) => {
    const geometry = feature.getGeometry();

    if (!(geometry instanceof Point)) {
      throw new Error("Node feature does not contain a Point geometry");
    }

    // Cast the coordinate to a 2-tuple [number, number]
    const [longitude, latitude] = toLonLat(
      geometry.getCoordinates()
    ) as [number, number];

    const id = feature.get("id");
    if (!id) {
      throw new Error("Node feature is missing an id");
    }

    return {
      clientId: String(id),
      longitude,
      latitude,
    };
  });

  const relations = mapData.connectionSource
    .getFeatures()
    .map((feature) => {
      const from = feature.get("from");
      const to = feature.get("to");

      if (!from || !to) {
        throw new Error("Connection feature missing 'from' or 'to' reference");
      }

      return {
        from: String(from),
        to: String(to),
      };
    });

  emit("save", {
    nodes,
    relations,
  });
}

defineExpose({
  saveMap,
});

let activeInteraction: Interaction | null = null;

function clearInteraction() {
  if ( mapData?.map && activeInteraction ) {
    mapData.map.removeInteraction( activeInteraction );
    activeInteraction = null;
  }
}

function activateMode( mode: DrawMode ) {
  if (!mapData) {
    return;
  }

  clearInteraction();

  if (mode === DrawMode.Default) {
      // Compute Addis Ababa extent for clipping bbox
      const extent = mapData.clipSource.getExtent() as [number, number, number, number];

      updateVoronoiDiagram(
        mapData.nodeSource,
        mapData.voronoiSource,
        extent
      );

      mapData.voronoiLayer.setVisible(true);
      console.log("Default mode: Voronoi zones displayed");
    } else {
      // Hide Voronoi while user is actively designing/drawing
      mapData.voronoiLayer.setVisible(false);
  }

  if (!props.setup) {
    return;
  }

  if (mode === DrawMode.Node) {
    activeInteraction = createNodeInteraction(
        mapData.nodeSource,
        mapData.clipSource,
      );

    mapData.map.addInteraction( activeInteraction );

    console.log( "Node mode enabled" );
  }

  if (mode === DrawMode.Line) {
    activeInteraction = createConnectionInteraction(
        mapData.nodeLayer,
        mapData.connectionSource,
      );

    mapData.map.addInteraction( activeInteraction );

    console.log( "Connection mode enabled" );
  }

  if (mode === DrawMode.Default) {
    console.log( "Default mode" );
  }
}

onMounted(async () => {
  mapData = createCityMap("map");

  // regenerate Voronoi
    mapData.clipSource.once("featuresloadend", () => {
      if (props.mode === DrawMode.Default) {
        refreshVoronoi();
      }
    });

  // load existing nodes Voronoi
  await loadNodes();

  // Activate whatever mode the
  // parent initially supplied.
  activateMode(props.mode);
});

watch(
  () => props.mode,
  (mode) => {
    activateMode(mode);
  },
);

watch(
  () => props.setup,
  (setup) => {
    if (!setup) {
      clearInteraction();
      return;
    }

    activateMode(props.mode);
  },
);

onUnmounted(() => {
  clearInteraction();

  if (mapData) {
    mapData.map.setTarget( undefined );

    mapData = null;
  }
});
</script>

<template>
  <div id="map"></div>
</template>

<style scoped>
#map {
  margin: 1rem;
  border: 2px dashed;
  width: 50%;
  height: 800px;
  background: transparent;
}
</style>
