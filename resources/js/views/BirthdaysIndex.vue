<template>
    <ContactsList endpoint="/api/birthdays" />
</template>

<script>
import ContactsList from "../components/ContactsList.vue";

export default {
    name: "ContactsIndex",

    components: {
        ContactsList,
    },
};
</script>

<style scoped></style>
