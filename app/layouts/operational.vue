<template>
    <div class="relative">
        <LazyHeaderOperational />
        <slot />
        <Footer
            class="footer sm:footer-horizontal bg-neutral text-neutral-content items-center p-4 absolute bottom-0"
        />
    </div>
</template>
