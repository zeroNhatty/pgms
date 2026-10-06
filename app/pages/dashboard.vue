<script setup lang="ts">
import { DrawMode } from '~/components/DrawMode';

</script>
<template>
    <main>
        <ClientOnly>
            <LazyCityMap  :setup="true"
            :mode="DrawMode.Default"/>
            <template #fallback>
                <div class="loading-state">Loading City Map...</div>
            </template>
        </ClientOnly>
    </main>
</template>

<style scoped>
.loading-state {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-color: #090d16;
    color: white;
}
</style>
