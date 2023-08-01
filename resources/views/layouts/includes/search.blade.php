<div id="app" class="mt-3">
    <form v-on:submit.prevent class="navbar-form">
        <div class="form-group">
            <input  type="text" class="form-control search-bar"
                    v-model="keyToSearch"  placeholder="Buscar...">
        </div>
        <template v-if="keyToSearch.length">
            <div class="anyClass">
                <template v-if="this.loading" v-cloak>
                    <div class="card">
                        <div class="card-header">
                            <i class="fa fa-circle-notch fa-spin fa-fw"></i>
                            <span>Cargando...</span>
                        </div>
                    </div>
                </template>
                <template v-if="this.found.bank.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Banco</strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <a class="list-group-item list-group-item-action"
                               :href="'/empresa/bancos/'+ (item.id - 1)"
                               v-for="item in this.found.bank">
                                <template v-if="item.nombre === ''">

                                </template>
                                <template v-else>
                                    @{{item.nombre}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.warehouse.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Bodegas</strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.warehouse"
                               :href="'/empresa/inventario/bodegas/'+ (item.id - 1) +'/edit'">
                                <template v-if="item.bodega === ''">

                                </template>
                                <template v-else>
                                    @{{item.bodega}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.inventory.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Inventario</strong>
                        </div>
                        <ul class="list-group list-group-flush">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.inventory"
                               :href="'/empresa/inventario/'+ item.id">
                                <template v-if="item.producto === ''">

                                </template>
                                <template v-else>
                                    @{{item.producto}} - @{{item.ref}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.contact.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Contactos</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.contact"
                               :href="'/empresa/contactos/'+ (item.id)">
                                <template v-if="item.nombre === ''">

                                </template>
                                <template v-else>
                                    @{{item.nombre}} - @{{ item.nit }}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.invoices.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Facturas de venta</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.invoices"
                               :href="'/empresa/facturas/'+ (item.nro)">
                                <template v-if="item.codigo === ''">

                                </template>
                                <template v-else>
                                    @{{item.codigo}} - <b>Cliente</b>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.invoicesOut.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Facturas de compra</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.invoicesOut"
                               :href="'/empresa/facturaspid/'+ (item.id)">
                                <template v-if="item.codigo === ''">

                                </template>
                                <template v-else>
                                    @{{item.codigo}} - <strong>Proveedor</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.billIn.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Pagos recibidos</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.billIn"
                               :href="'/empresa/ingresos/'+ (item.id)">
                                <template v-if="item.nro === ''">

                                </template>
                                <template v-else>
                                    @{{item.nro}} - <strong>Cliente</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.billOut.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Pagos</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.billOut"
                               :href="'/empresa/pagos/'+ (item.id)">
                                <template v-if="item.nro === ''">

                                </template>
                                <template v-else>
                                    @{{item.nro}} - <strong>Cliente</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.credit.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Notas Crédito</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.credit"
                               :href="'/empresa/notasdebito/'+ (item.nro)">
                                <template v-if="item.nro === ''">

                                </template>
                                <template v-else>
                                    @{{item.nro}} - <strong>Cliente</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.debit.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Notas Débito</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.debit"
                               :href="'/empresa/notacredito/'+ (item.nro)">
                                <template v-if="item.nro === ''">

                                </template>
                                <template v-else>
                                    @{{item.nro}} - <strong>Cliente</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.order.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Ordenes de compra</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.order"
                               :href="'/empresa/ordenes/'+ (item.orden_nro)">
                                <template v-if="item.nro === ''">

                                </template>
                                <template v-else>
                                    @{{item.orden_nro}} - <strong>Proveedor:</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.remission.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Remisiones</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.remission"
                               :href="'/empresa/remisiones/'+ (item.id)">
                                <template v-if="item.nro === ''">

                                </template>
                                <template v-else>
                                    @{{item.nro}} - <strong>Cliente</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
                <template v-if="this.found.quotation.length">
                    <div class="card">
                        <div class="card-header">
                            <strong class="">Cotizaciones</strong>
                        </div>
                        <ul class="list-group">
                            <a class="list-group-item list-group-item-action"
                               v-for="item in this.found.quotation"
                               :href="'/empresa/cotizaciones/'+ (item.cot_nro)">
                                <template v-if="item.cot_nro === ''">

                                </template>
                                <template v-else>
                                    @{{item.cot_nro}} - <strong>Cliente</strong>: @{{item.nombrecliente}}
                                </template>
                            </a>
                        </ul>
                    </div>
                </template>
            </div>
        </template>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/vue"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.18.0/axios.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lodash@4.13.1/lodash.min.js"></script>

<script>

    new Vue({
        el:'#app',
        data: {
            found:  [],
            keyToSearch: '',
            loading: false,
        },
        watch: {
            keyToSearch: function () {
                this.loading = true;
                this.funcion();
            }
        },
        created: function () {
            this.funcion = _.debounce(this.search, 1500);
        },
        methods: {
            search() {
                axios.get('/api/{{Auth::user()->empresa}}/getDataSearch/'+this.keyToSearch).then(response => {
                    this.loading = false;
                    this.found = response.data;
                }).catch(e => {
                    console.log(e);
                });
            }
        }
    });

</script>
<style scoped>

    .anyClass {
        top:40px;
        position:absolute;
        height: 200px;
        overflow-y: scroll;
        background-color: white;

    }

    .anyClass {
        top:55px;
        position:absolute;
        height: 200px;
        width: 30%;
        overflow-y: scroll;
        border-radius: 8px;
    }

    @media (max-width: 620px) {

        .anyClass {
            top:105px;
            width: 65%;
        }

    }

    @media (max-width: 480px) {

        .anyClass {
            top:105px;
            width: 65%;
        }


    }

    .list-group-item {
        background-color: #ffffff;
        border-radius: 0;
        color: black;
        font-weight: lighter;
    }
    .list-group-item:hover {
        background-color: #f9f6f2 ;
    }

    .card-header{
        background-color: #e8e9ee;
        color: black;
    }
 </style>

