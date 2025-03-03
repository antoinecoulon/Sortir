import { startStimulusApp, registerControllers } from "vite-plugin-symfony/stimulus/helpers"
import { registerVueControllerComponents } from "vite-plugin-symfony/stimulus/helpers/vue"

// register Vue components before startStimulusApp
registerVueControllerComponents(import.meta.glob('./vue/controllers/**/*.vue'))

const app = startStimulusApp();
registerControllers(
    app,
    import.meta.glob(
        "./controllers/*_controller.js",
        {
            query: "?stimulus",
            eager: true,
        },
    ),
);