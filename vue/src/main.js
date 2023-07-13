import { createApp, h } from "vue";

import App from "@/app.vue";
import ErrorBoundary from "@/error-boundary.vue";

createApp(h(ErrorBoundary, () => h(App))).mount("#app");
