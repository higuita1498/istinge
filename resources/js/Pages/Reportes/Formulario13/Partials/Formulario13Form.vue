<script setup lang="ts">
import { computed } from "vue";
import { useForm, useField } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import * as zod from "zod";

defineProps<{
    errors: string;
}>();

const validationSchema = computed(() => {
    return toTypedSchema(
        zod.object({
            initialDate: zod
                .string()
                .refine((value: string) => zod.date().safeParse(value))
                .nullable(),
            finalDate: zod
                .string()
                .refine((value: string) => zod.date().safeParse(value))
                .nullable(),
        })
    );
});

const form = useForm({
    validationSchema,
});

const { value: initialDate } = useField<string>("initialDate", null, {
    initialValue: "",
});
const { value: finalDate } = useField<string>("finalDate", null, {
    initialValue: "",
});

const onSubmit = form.handleSubmit(async (values) => {
    window.open(
        `formulario-1-3/generate?initialDate=${values.initialDate}&finalDate=${values.finalDate}`
    );
});
</script>

<template>
    <template v-if="Object.keys(JSON.parse(errors)).length > 0">
        <div class="alert alert-danger" role="alert">
            <ul>
                <li v-for="error in JSON.parse(errors)" :key="error">
                    <!--
                        TODO: Por el momento se deja el primer error de los errores,
                        debería corregirse
                    -->
                    {{ error[0] }}
                </li>
            </ul>
        </div>
    </template>

    <form @submit="onSubmit">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="col mb-3">
                    <label class="form-label">Fecha inicio</label>
                    <input
                        v-model="initialDate"
                        type="date"
                        class="form-control"
                    />
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="col mb-3">
                    <label class="form-label">Fecha final</label>
                    <input
                        v-model="finalDate"
                        type="date"
                        class="form-control"
                        :min="initialDate"
                    />
                </div>
            </div>
            <div class="col text-center">
                <button
                    class="btn btn-primary"
                    :disabled="
                        !form.meta.value.valid || form.isSubmitting.value
                    "
                >
                    Generar formulario
                </button>
            </div>
        </div>
    </form>
</template>
