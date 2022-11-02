import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

let user = document.getElementById("app").getAttribute("user");
user = JSON.parse(user);

Alpine.start();

import { createApp } from "vue";

import router from "./routes";

import App from "./components/App.vue";

const app = createApp(App, { user });
app.use(router);
app.mount("#app");
