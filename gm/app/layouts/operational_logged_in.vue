<script setup lang="ts"></script>
<template>
    <div>
        <LazyHeaderOperationalLogged />
        <slot />
        <Footer
            class="footer sm:footer-horizontal bg-neutral text-neutral-content items-center p-4 bottom-0"
        />
    </div>
</template>
