<template>
    <li class="nav-item">
        <form v-on:submit.prevent>
            <div class="form-group">
                <input type="text" class="form-text"
                       v-model="keyToSearch" placeholder="Buscar...">
            </div>
            <template v-if="keyToSearch.length">
                <div class="anyClass">
                    <template v-if="search.bank.length">
                        <div class="card bg-transparent">
                            <div class="card-header">
                                <span class="text-white">Banco</span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item list-group-item-action"
                                    v-for="item in search.bank.slice(0,5)">
                                    <template v-if="item.nombre === ''">

                                    </template>
                                    <template v-else>
                                        <a class="btn btn-light btn-sm"
                                           :href="'/empresa/bancos/'+ (item.id - 1)">
                                            {{item.nombre}}
                                        </a>
                                    </template>
                                </li>
                            </ul>
                        </div>
                    </template>
                    <template v-if="search.warehouse.length">
                        <div class="card bg-transparent">
                            <div class="card-header">
                                <span class="text-white">Bodegas</span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item list-group-item-action"
                                    v-for="item in search.warehouse.slice(0,5)">
                                    <template v-if="item.bodega === ''">

                                    </template>
                                    <template v-else>
                                        <a class="btn btn-light btn-sm"
                                           :href="'/empresa/inventario/bodegas/'+ (item.id - 1) +'/edit'">
                                            {{item.bodega}}
                                        </a>
                                    </template>
                                </li>
                            </ul>
                        </div>
                    </template>
                    <template v-if="search.inventory.length">
                        <div class="card bg-transparent">
                            <div class="card-header">
                                <span class="text-white">Inventario</span>
                            </div>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item list-group-item-action"
                                    v-for="item in search.inventory.slice(0,5)">
                                    <template v-if="item.producto === ''">

                                    </template>
                                    <template v-else>
                                        <a class="btn btn-light btn-sm"
                                           :href="'/empresa/inventario/'+ item.id">
                                            {{item.producto}}
                                        </a>
                                    </template>
                                </li>
                            </ul>
                        </div>
                    </template>
                    <template v-if="search.contact.length">
                        <div class="card bg-transparent">
                            <div class="card-header">
                                <span class="text-white">Contactos</span>
                            </div>
                            <ul class="list-group">
                                <li class="list-group-item list-group-item-action"
                                    v-for="item in search.contact.slice(0,5)">
                                    <template v-if="item.nombre === ''">

                                    </template>
                                    <template v-else>
                                        <a class="btn btn-light btn-sm"
                                           :href="'/empresa/contactos/'+ (item.id - 1)">
                                            {{item.nombre}}
                                        </a>
                                    </template>
                                </li>
                            </ul>
                        </div>
                    </template>
                </div>
            </template>
        </form>
    </li>
</template>

<script>
    export default {
        name: "searchbar",
        data(){
            return {
                found:  [],
                keyToSearch: '',
            }
        },
        created(){
            axios.get('/getAllData').then(response => {this.found = response.data});
        },
        methods:{
            test(){
                console.log(this.found.inventory);
            }
        },
        computed: {
            search: function () {

                let temporalData;

                let bank =  this.found.bank.filter((item) => {
                    if (item.nombre.match(this.keyToSearch)) {
                        temporalData = {
                            "id": item.id,
                            "nombre": item.nombre,
                        };
                    }
                    return temporalData;
                });

                let warehouse = this.found.warehouse.filter((item) => {
                    if (item.bodega.match(this.keyToSearch)) {
                        temporalData = {
                            "id": item.id,
                            "bodega": item.bodega,
                        };
                    }
                    return temporalData;
                });
                let inventory = this.found.inventory.filter((item) => {
                    if (item.producto.match(this.keyToSearch)) {
                        temporalData = {
                            "id": item.id,
                            "producto": item.producto,
                        };
                    }
                    return temporalData;
                });
                let contact =  this.found.contact.filter((item) => {
                    if (item.nombre.match(this.keyToSearch)) {
                        temporalData = {
                            "id": item.id,
                            "nombre": item.nombre,
                        };
                    }
                    return temporalData;
                });;

                return {
                    'contact'       : contact,
                    'bank'          : bank,
                    'inventory'     : inventory,
                    'warehouse'     : warehouse,
                };
            }
        }
    }
</script>

<style scoped>
    .anyClass {
        height: 200px;
        overflow-y: scroll;
    }
    .list-group-item {
        background-color: #D08E51;
        border-radius: 0;
        color: #fff;
    }
</style>
