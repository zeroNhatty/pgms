import Echo from "laravel-echo";
import Pusher from "pusher-js";

declare global {
  interface Window {
    Pusher: typeof Pusher;
    Echo: Echo<any>;
  }
}

export default defineNuxtPlugin(() => {
  window.Pusher = Pusher;

  const config = useRuntimeConfig();

  const echo = new Echo({
    broadcaster: "reverb",
    key: config.public.reverbAppKey,
    wsHost: config.public.reverbHost,
    wsPort: Number(config.public.reverbPort),
    wssPort: Number(config.public.reverbPort),
    forceTLS: false,
    //forceTLS: config.public.reverbScheme === "https",
    // enabledTransports: ["ws"],
    enabledTransports: ["ws", "wss"],
  });

  echo.connector.pusher.connection.bind("state_change", (states: any) => {
    console.log("Reverb state:", states.previous, "→", states.current);
  });

  echo.connector.pusher.connection.bind("error", (error: any) => {
    console.error("Reverb error:", error);
  });

  return {
    provide: {
      echo,
    },
  };
});
