<style>
    #period-billings-table th {
        color: white;
        background: linear-gradient(182deg, #1b3354 31.03%, #001128 99.96%);
        text-align: center;

    }

    .periodo-facturacion {
        display: flex;
        align-items: center;
        gap: 2rem;
        background: #d8d7fe;
        border-radius: 69px;
        border: 1px solid #b7bbea;
        padding: 6px 8px 6px 22px;
        width: fit-content;
        margin: auto;
        color: #1d1f38 !important;



        h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;

        }

        a {
            background: #1d1f38;
            color: #fff;
            padding: 10px 14px;
            border-radius: 69px;
            font-weight: 600;
            transition: all 250ms ease;

            &:hover {
                background: #656697;
                transition: all 250ms ease;
                text-decoration: none;
            }

        }
    }

    @media(width < 1000px){
        .periodo-facturacion{
            width: 100%;
            display: grid;
            margin: 1rem 0; 
            gap: 1rem;
            padding: 1rem;
            border-radius: 14px;
            text-align: center;

            >h3{
                font-size: 18px;
            }

            >a{
                padding: 8px 12px;
                font-size: 14px;
            }
            
        }
    }
</style>




<div class="card-body">

    <div class="periodo-facturacion">
        <h3>Periodos de facturación
        </h3>
        <a href="{{ route('plans.billings.create', ['idPlan' => $idPlan]) }}" class="">
            Crear periodo
        </a>
    </div>


    <table id="period-billings-table" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Dias de prorroga</th>
                <th>Meses</th>
                <th>Descuento</th>
                <th>Es favorito</th>
                <th>Adicion de facturas</th>
                <th>Adicion de monto</th>
                <th>Habilitado</th>
                <th>Creacion</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($periodBillings as $periodBilling)
                <tr>
                    <td>{{ $periodBilling->id }}</td>
                    <td>{{ $periodBilling->extension_days }}</td>
                    <td>{{ $periodBilling->months }}</td>
                    <td>{{ $periodBilling->discount }}</td>
                    <td>{{ $periodBilling->is_fav ? 'Si' : 'No' }}</td>
                    <td>{{ $periodBilling->additon_invoices }}</td>
                    <td>{{ $periodBilling->additon_billing }}</td>
                    <td>{{ $periodBilling->is_active ? 'Si' : 'No' }}</td>
                    <td>{{ $periodBilling->created_at }}</td>
                    <td><a href="{{ route('plans.billings.show', $periodBilling->id) }}">Ver</a> | <a
                            href="{{ route('plans.billings.show', $periodBilling->id) }}?openEdit=true">Edit</a></th>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>



<script>
    $(document).ready(function() {
        $('#period-billings-table').DataTable();
    });
</script>
