<template>
    <div>
        <LazyHeader />
        <slot />
        <Footer
            class="footer sm:footer-horizontal bg-neutral text-neutral-content items-center p-4"
        />
    </div>
</template>
