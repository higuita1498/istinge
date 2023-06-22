<script setup lang="ts">
import { computed } from "vue";
import { useForm, useField } from "vee-validate";
import { toTypedSchema } from "@vee-validate/zod";
import { DateTime } from "luxon";
import axios from "axios";
import * as zod from "zod";

const validationSchema = computed(() => {
    return toTypedSchema(
        zod.object({
            initialDate: zod
                .string()
                .refine((value: string) => zod.date().safeParse(value)),
            finalDate: zod
                .string()
                .refine((value: string) => zod.date().safeParse(value)),
        })
    );
});

const form = useForm({
    validationSchema,
});

const { value: initialDate } = useField<string>("initialDate");
const { value: finalDate } = useField<string>("finalDate");

const onSubmit = form.handleSubmit(async (values) => {
    const response = await axios.post("formulario-1-3", {
        values,
    });
});
</script>

<template>
    <form @submit="onSubmit">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="col mb-3">
                    <label class="form-label">Fecha inicio</label>
                    <input
                        v-model="initialDate"
                        type="date"
                        class="form-control"
                        required
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
                        required
                        :min="initialDate"
                        :disabled="!initialDate"
                    />
                </div>
            </div>
            <div class="col text-center">
                <button
                    class="btn btn-primary"
                    :disabled="
                        !form.meta.value.dirty || form.isSubmitting.value
                    "
                >
                    Generar formulario
                </button>
            </div>
        </div>
    </form>
</template>
