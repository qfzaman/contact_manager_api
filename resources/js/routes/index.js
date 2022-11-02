import { createRouter, createWebHistory } from "vue-router";
import Home from "../views/Home.vue";
import ContactsCreate from "../views/ContactsCreate.vue";
import ContactsShow from "../views/ContactsShow.vue";
import ContactsEdit from "../views/ContactsEdit.vue";
import ContactsIndex from "../views/ContactsIndex.vue";
import BirthdaysIndex from "../views/BirthdaysIndex.vue";
import Logout from "../views/Logout.vue";

const routes = [
    {
        path: "/",
        component: Home,
        meta: { title: "Welcome" },
    },
    {
        path: "/contacts",
        component: ContactsIndex,
        meta: { title: "Contacts" },
    },
    {
        path: "/contacts/create",
        component: ContactsCreate,
        meta: { title: "Add New Contact" },
    },
    {
        path: "/contacts/:id",
        component: ContactsShow,
        meta: { title: "Details for Contact" },
    },
    {
        path: "/contacts/:id/edit",
        component: ContactsEdit,
        meta: { title: "Edit Contact" },
    },
    {
        path: "/birthdays",
        component: BirthdaysIndex,
        meta: { title: "This Month's Birthdays" },
    },
    {
        path: "/logout",
        component: Logout,
    },
];

const router = createRouter({
    routes,
    history: createWebHistory(),
});

export default router;
