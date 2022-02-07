<hr>
<div id="pagination">
    <form class="form-inline" v-on:submit.prevent>
        <div class="form-group  mx-sm-3 mb-2">
            <div class="input-group">
                <div class="input-group-prepend">
                    <div class="input-group-text">Ir a:</div>
                </div>
                <input class="form-control form-control-sm" type="number" placeholder="Página..." id="page"
                    v-model="page">
            </div>
        </div>
        <div class="form-group mb-2">
            <button type="button" class="btn btn-primary btn-sm"
                v-on:click="redirect">
                Ir
            </button>
        </div>
    </form>
</div>

<script>

    new Vue({
        el:'#pagination',
        data: {
            url: '',
            page: '',
            order: '',
            orderby: '',
            orderby: '',
            orderby: '',
            orderby: '',
            orderby: '',
            orderby: '',
            orderby: '',
            appends: '',
            hasAppends: false,
        },
        mounted(){
            //Obtenemos la URL y sacamos los strings con varibales get que ahí se encuentran
            let url         = window.location.href;
            this.url        = url.split('?')[0];
            this.appends    = url.split('lista')[1];

            //Determinamos si se ha aplicado algún filtro
            if (this.appends != null){
                this.hasAppends = true;
            }

            //Buscamos las variables
            let search      = new URLSearchParams(window.location.search);

            this.page       = search.get('page');
            this.order      = search.get('order')       == null ?  1 : search.get('orderby');
            this.orderby    = search.get('orderby')     == null ?  1 : search.get('order');

        },
        methods: {
            redirect: function (){

                if(this.hasAppends){
                    window.location.href =
                        this.url+'?'+'orderby='+this.orderby+'&'+'order='+this.order+'&'+'lista='+this.appends;
                }else{
                    window.location.href =
                        this.url+'?'+'orderby='+this.orderby+'&'+'order='+this.order+'&'+'page='+this.page;
                }
            }
        }

    });

</script>

