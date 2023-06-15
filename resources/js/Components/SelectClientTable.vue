<script setup>
import Pagination from "./Pagination.vue";

import axios from "axios";
import { ref } from "vue";

const clients = ref([]);
const paginationData = ref(null);
const isLoading = ref(true);

async function select(client) {
    console.log(client);
}

function getClientsByPage(page = 1) {
    getClients(`/empresa/radicados/find-clients?page=${page}`);
}

async function getClients(url = "/empresa/radicados/find-clients") {
    console.log(url);
    isLoading.value = true;

    try {
        const response = await axios.get(url);
        paginationData.value = response.data;
        clients.value = paginationData.value.data;

        console.log(paginationData);
    } catch (e) {
    } finally {
        isLoading.value = false;
    }
}

getClients();
</script>

<template>
    <template v-if="paginationData">
        <div class="position-relative container">
            <template v-if="isLoading"> Carganding. </template>

            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Nombre</th>
                        <th scope="col">Cédula</th>
                        <th scope="col">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="client in clients" :key="client.id">
                        <td>{{ client.nombre }}</td>
                        <td>{{ client.nit }}</td>
                        <td>
                            <button
                                class="btn btn-primary btn-sm"
                                @click="select(client)"
                            >
                                Seleccionar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <Pagination :paginationData="paginationData" @search="getClients"/>
        </div>
    </template>
    <template v-else> Cargando... </template>
</template>
