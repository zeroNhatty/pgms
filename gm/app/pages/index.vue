<template>
  <div>
    <div
        class="hero min-h-screen"
        style="background-image: url(https://images.unsplash.com/photo-1597502310092-31cdaa35b46d?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D);"
    >
      <div class="hero-overlay bg-opacity-60"></div>
      <div class="hero-content text-neutral-content text-center">
        <div class="max-w-md">
          <h1 class="mb-5 text-5xl font-bold" id="home">Power Grid Monitor</h1>
          <p class="mb-5">
            This is a theoretical simulator for a power grid monitoring system including power status, down time and fault pinpointing.
          </p>
          <div class="space-x-4 flex items-center justify-center">
            <NuxtLink class="btn btn-primary" to="dashboard">Get Started</NuxtLink>
            <a class="btn btn-soft btn-secondary gap-1" href="#more-about-pgm">
              <span class="flex items-center">Learn More</span>
              <span class="material-symbols-outlined">
                arrow_forward
              </span>
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="hero bg-base-200 min-h-fit py-16">
      <div class="hero-content text-left">
        <div class="max-w-md">
          <h1 class="text-5xl font-bold" id="more-about-pgm">What is PGM?</h1>
          <p class="py-6">
            <code class="bg-base-300 px-1.5 py-0.5 rounded">Power Grid Monitor</code> or PGM for short is a multi-layer web application that
            aims to showcase what a power grid monitor system would do to monitor individual power lines and nodes.
          </p>
          <NuxtLink class="btn btn-primary" to="dashboard">Get Started</NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>