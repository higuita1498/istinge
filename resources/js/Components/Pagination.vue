<script setup>
import { ref, watch } from "vue";

const PAGINATION_SIZE = 2;

const props = defineProps({
    paginationData: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(["search"]);

const paginationNumbers = ref([]);

function emitPage(page) {
    emit("search", `${props.paginationData.path}?page=${page}`);
}

function emitUrl(url) {
    emit("search", url);
}

function generatePaginationNumbers() {
    paginationNumbers.value.length = 0;

    for (
        var i = Math.max(
            1,
            props.paginationData.current_page - PAGINATION_SIZE
        );
        i <=
        Math.min(
            props.paginationData.last_page,
            props.paginationData.current_page + PAGINATION_SIZE
        );
        i++
    ) {
        paginationNumbers.value.push(i);
    }
    console.log(paginationNumbers.value);
}

watch(props, (_value, _oldValue) => {
    generatePaginationNumbers();
});

generatePaginationNumbers();
</script>

<template>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            <li class="page-item">
                <button
                    class="page-link"
                    :class="{
                        disabled: paginationData.prev_page_url == null,
                    }"
                    @click="emitUrl(paginationData.prev_page_url)"
                >
                    Atrás
                </button>
            </li>
            <li class="page-item" v-for="num in paginationNumbers" :key="num">
                <button
                    class="page-link"
                    :class="{
                        active: paginationData.current_page == num,
                    }"
                    @click="emitPage(num)"
                >
                    {{ num }}
                </button>
            </li>
            <li class="page-item">
                <button
                    class="page-link"
                    :class="{
                        disabled: paginationData.next_page_url == null,
                    }"
                    @click="emitUrl(paginationData.next_page_url)"
                >
                    Siguiente
                </button>
            </li>
        </ul>
    </nav>
</template>
