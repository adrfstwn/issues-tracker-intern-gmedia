import "./assets/main.css";
import { createApp } from "vue";
import { createPinia } from "pinia";
import App from "./App.vue";
import router from "./router";
import * as Sentry from "@sentry/vue";
const app = createApp(App);

// Sentry.init({
//   app,
//   dsn: "https://55364a6f5b514bb896ed1c1ec6b46658@sentry.varx.ai/60",
//   integrations: [
//     Sentry.browserTracingIntegration({ router }),
//     Sentry.replayIntegration(),
//   ],
//   tracesSampleRate: 1.0,
//   autoSessionTracking: false, 

// });

app.use(createPinia());
app.use(router);

app.mount("#app");
