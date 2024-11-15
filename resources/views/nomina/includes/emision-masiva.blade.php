<style>
    .modal.fades.show {
        display: flex !important;
        -webkit-animation: slide-arriba 0.5s cubic-bezier(0, 0, .2, 1) both;
        animation: slide-arriba 0.5s cubic-bezier(0, 0, .2, 1) both;
    }

    @keyframes slide-arriba {
        0% {
            opacity: 0;
            -webkit-transform: translateY(100px);
            transform: translateY(100px);
        }

        100% {
            opacity: 1;
            -webkit-transform: translateY(0px);
            transform: translateY(0px);
        }
    }

    .modal-emision__header {
        display: flex;
        background: rgb(255, 255, 255);
        width: fit-content;
        align-items: center;
        gap: 40rem;
        margin: auto;
        padding: 1rem;
        border-radius: 10px 10px 0px 0px;

        @media (max-width: 868px) {
            width: auto;
            gap: unset;
            justify-content: space-evenly;
        }

        &>h4 {
            font-size: 22px;
            font-weight: 700;
        }

        @media screen and (max-width: 458px) {
            &>h4 {
                font-size: 18px;
            }
        }

        &>button {
            all: unset;
            font-size: 34px;

            &:hover {
                transition: all 0.2s ease-in-out;
                transform: scale(1.2);
            }
        }

    }

    .modal-facturar__item-cont {
        display: grid;
        border-radius: 8px;
        width: 100%;
        padding: 1rem;
        box-shadow: 0px 0px 0px 1px rgba(0, 0, 0, 0.08), 0px 6px 24px 0px rgba(0, 0, 0, 0.05);
        transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);

        .modal-emision__bar {
            width: 100%;
            background: gray;
            height: 6px;
        }
    }

    .modal-facturar__item-cont--ok {
        display: grid;
        border: 1px solid #80808057;
        border-radius: 8px;
        width: 100%;
        padding: 1rem;
        transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);

        .modal-emision__bar {
            width: 100%;
            background: #63ECBC;
            height: 6px;
        }

        svg {
            color: #63ECBC;
        }
    }

    .modal-facturar__item-cont--error {
        display: grid;
        border: 1px solid #80808057;
        border-radius: 8px;
        width: 100%;
        padding: 1rem;
        transition: all 150ms cubic-bezier(0.4, 0, 0.2, 1);

        .modal-emision__bar {
            width: 100%;
            background: #ff9900;
            height: 6px;
        }

        svg {
            color: #ff9900;
        }
    }

    .modal-masiva__name {
        color: #838383;
    }

    .code-document {
        color: #838383;
        font-size: 12px;
    }

    .modal-masiva__value {
        font-size: 22px;
        font-weight: 700;
    }

    .modal-emision__content {
        background: #fff;
        height: auto;
        max-height: 50vh;
        border-radius: 0 0 26px 26px;
        padding: 2rem 3rem;
        display: grid;
        place-items: center;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        overflow: auto;
        animation: slide-arriba 500ms cubic-bezier(0, 0, .2, 1) both;

        @media (max-width: 868px) {
            grid-template-columns: 1fr 1fr;
        }
    }

    .modal-emision__content.details {
        display: flex;
        flex-direction: column;
        align-items: baseline;

        >h3 {
            font-size: 22px;
        }
    }

    .modal-emision__content--item {
        height: 55px;
        align-items: center;
        gap: 1rem;
        display: flex;
        justify-content: space-between;
        padding: 0 20px;
        width: 100%;
        box-shadow: 2px 2px 6px 0px #d6d6d6;


        .code-document {
            font-size: 1.5em;
        }

        >svg {
            cursor: pointer;
        }
    }


    .modal-emision__footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem;
    }

    .modal-emision__btn-emitir {
        display: flex;
        align-items: center;
        padding: 13px 20px;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        color: #fff !important;
        justify-content: center;
        align-items: center;
        gap: 8px;
        border-radius: 12px;
        background-image: linear-gradient(182deg, #1b3354 31.03%, #001128 99.96%);
        transition: transform 200ms ease 0s;

        &:hover {
            transform: translateY(-2px);
        }

    }

    .modal-emision__btn-emitir.imprimir {
        background: linear-gradient(182deg, #65ecbd 31.03%, #26c78f 99.96%);
        color: #011227 !important;
        border: 1px solid #60c2a0 !important;
    }

    .modal-emision__items-length {
        color: #808080;
        font-weight: 600;
    }

    .modal-emision__breadcrumbs {
        background: #63ECBC;
        width: 100%;
        text-align: center;
        padding: 1rem;
        border-radius: 12px;

        >h3 {
            margin: 0;
            font-weight: 600;
        }
    }

    .menu-info-nomina {
        display: grid;
        padding: 1rem 0 0 0;
        animation: slide-arriba-2 400ms cubic-bezier(0, 0, .2, 1) both;
    }

    @keyframes slide-arriba-2 {
        0% {
            opacity: 0;
            -webkit-transform: translateY(20px);
            transform: translateY(20px);
        }

        100% {
            opacity: 1;
            -webkit-transform: translateY(0px);
            transform: translateY(0px);
        }
    }

    .modal-emision__content--options {
        display: flex;
        justify-content: space-between;
        margin: 10px 0;
    }

    .modal-emision__btn-details {
        cursor: pointer;
        height: 36px;
        padding: 2px 16px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #fff;
        background: linear-gradient(182deg, #1b3354 31.03%, #001128 99.96%);
        width: -moz-fit-content;
        width: fit-content;
        font-size: 15px;
        font-weight: 700;
        border-radius: .65rem;
        box-shadow: 0 4px 8px #00207026;
        transition: all .3s ease;

        &:hover {
            transition: all .3s ease;
            opacity: .8;
            color: white;
            text-decoration: none;

            >svg {
                transition: all .3s ease;
                transform: translateY(2px);
            }
        }
    }

    .details-nom-item {
        display: grid;
        background: #D9F3F0;
        margin: 0 0 10px 0;
        border-radius: 8px;
        padding: 4px 12px;
        transition: all 150ms ease;

        &:hover {
            transition: all 300ms ease;
            background: #f7f2f2;
        }

        >strong {
            display: flex;
            justify-content: space-between;
            color: #000000;
        }
    }


    /* Estilos para la tabla de los detalles de emision abajo */

    .modal-emision__table {
        width: 100%;
        border-collapse: collapse;
    }

    .modal-emision__table th {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: center;
    }

    .modal-emision__table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
    }

    .modal-emision__table th {
        background-color: #d7e9ff;
    }

    #icon-status-bill {
        cursor: pointer;
    }

    /* Estilos para la barra de progeso abajo */

    .modal-emision__content--progress {
        height: 2rem;
        padding: 4px;
        box-shadow: 0 0 0 2px #d3d3d3;
        border-radius: 9px;
        background: #f2f2f2;
    }

    .progress-bar {
        border-radius: 7px;
        height: 100%;
    }

    .progress-bar {
        background: linear-gradient(90deg, #ddfff4 0%, #63ECBC 100%);
    }
</style>


<script>
    var dataEmitida = [];
    var countEmitidas = 0;
    var countRechazadas = 0;
    var valImprimir = [];
    var isEmail = false;


    function imprimirMultiple(){
        isEmail = true;
        emitirLote(email = true);
    }

    const emisionMasiva = () => {

        $('#detalles-emision-modal').remove();
        $('.modal-backdrop').remove();
        $('#bar-progress-emitir').css('width', '0%');


        if ($('#emision-masiva').length > 0) {
            $('#emision-masiva').modal('hide');
        }

        let table = $('#table-show-empleados').DataTable();
        let selectedRowCount = table.rows('.selected').data().length;

        items = [];
        for (i = 0; i < selectedRowCount; i++) {
            let rowData = table.rows('.selected').data()[i];
            console.log(rowData);

            let htmlString = rowData[5];

            let dataNominaMatch = htmlString.match(/data-nomina="(\d+)"/);
            let dataNomina = dataNominaMatch ? dataNominaMatch[1] : null;


            items.push({
                codigo: extractText(rowData[0], ),
                name: extractText(rowData[1]),
                value: rowData[3],
                idNomina: dataNomina
            });
        }
       
        if (items.length > 0) {

            $('#emision-masiva').remove();

            let billItem = items.map((item, index) => `
                
                <article class="modal-facturar__item-cont" data-idnomina="${item.idNomina}">
                    <div class="d-flex justify-content-between">
                        <span class="code-document" code="${item.idNomina}">${item.codigo}</span>
                        <span id="icon-status-bill">
                            <svg class="remove-bill" style="float: right;" viewBox="0 0 1024 1024" data-index=${index} height="22" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
                                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                                <g id="SVGRepo_iconCarrier">
                                    <path fill="currentColor" d="M195.2 195.2a64 64 0 0 1 90.496 0L512 421.504 738.304 195.2a64 64 0 0 1 90.496 90.496L602.496 512 828.8 738.304a64 64 0 0 1-90.496 90.496L512 602.496 285.696 828.8a64 64 0 0 1-90.496-90.496L421.504 512 195.2 285.696a64 64 0 0 1 0-90.496z"></path>
                                </g>
                            </svg>
                        </span>
                    </div>
                    <span class="modal-masiva__name">${item.name}</span>
                    <div class="modal-emision__content--options">
                        <span class="modal-masiva__value">${item.value}</span>
                        <button class="modal-emision__btn-details" onclick="mostrarDetallesNomina(${item.idNomina});">
                            Detalles
                            <svg xmlns="http://www.w3.org/2000/svg" height="18" viewBox="0 0 24 24" fill="none">
                                <path d="M12 20L12 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M17 15C17 15 13.3176 20 12 20C10.6824 20 7 15 7 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="modal-emision__bar"></div>
                </article>
                `).join('');

            let templateEmisionMasiva = `
                <div class="modal fades" id="emision-masiva">
                    <div class="d-block m-auto" style="height: auto; background: #fff; border-radius: 10px 10px 26px 26px;">
                        <header class="modal-emision__header">
                            <h4>Proceso de emisión masiva, <span>{{ ucfirst($date->monthName) . ' ' . $date->format('Y') }}</span></h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"
                                title="Cerrar modal nominas en lote">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </header>
                    
                        <section class="modal-emision__content">
                            ${billItem}
                        </section>

                        <article class="px-5 text-center" style="display: none;" id="cont-progress-bar">
                            <div class="d-flex justify-content-center align-items-center" style="gap: 8px;">
                                <span id="percentage-number" style="font-weight: 500; color: gray;"> 0%</span>
                                <img class="mb-3" src="https://github.com/Tarikul-Islam-Anik/Microsoft-Teams-Animated-Emojis/raw/master/Emojis/Activities/Party%20Popper.png?raw=true" alt="Confettu" title="Fiestica" id="party-popper" width="31" height="31" style="max-width: 100%; display: none;">
                            </div>
                           <div class="modal-emision__content--progress" role="progressbar" aria-label="Animated striped example" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">   
                                <div class="progress-bar" style="width: 0%" id="bar-progress-emitir" data-percentage="0"></div>
                            </div>
                        </article>

                    <footer class="modal-emision__footer"> 
                        <span class="modal-emision__items-length">${items.length} nominas</span>
                        
                        <div class="d-flex" style="gap: 15px;"> 

                            <button class="modal-emision__btn-emitir" onclick="emitirLote()" id="btn-emitir">
                                Emitir
                            </button>    
                        </div>

                        <div style="display: none; gap: 15px;" id='boton-after-emitir'>
                            <button class="modal-emision__btn-emitir imprimir" onclick="imprimirMultiple()">
                                Enviar por correo
                            </button>
                            <button class="modal-emision__btn-emitir" id="detalles-emision">
                                Detalles de la emision
                            </button>
                        </div>
                    </footer>
                    </div>
                </div>`;

            $('body').append(templateEmisionMasiva);

            $('#emision-masiva').modal('show');
        } else {
            Swal.fire({
                type: 'warning',
                title: 'Atención',
                text: 'Debe seleccionar al menos una nomina',
            });
            1
        }

    }

    $(document).on('click', '.remove-bill', function(e) {
        e.preventDefault();

        let index = $(this).data('index');

        $(this).closest('.modal-facturar__item-cont').fadeOut(400, function() {
            $(this).remove();
        });

        items.splice(index, 1);

        $('.modal-emision__items-length').text(`${items.length} nominas`);
    });

    const mostrarDetallesNomina = (idNomina) => {
        // Si el menu-info-nomina ya existe, lo ocultamos, sino, procedemos y asi evitamos hacer peticiones innecesarias
        const contenedorPadre = $(`.modal-facturar__item-cont[data-idnomina="${idNomina}"]`);
        if (contenedorPadre.find('.menu-info-nomina').length) {
            contenedorPadre.find('.menu-info-nomina').toggle();
        } else {
            $.ajax({
                url: `/empresa/nomina-get-resumen/${idNomina}`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        let data = response.data;
                        let itemNomina = '';
                        const clavesMapping = {
                            "salario": "Salario",
                            "subsidio_de_transporte": "Subsidio de Transporte",
                            "horas_extras_ordinarias_y_recargos": "Horas Extras Ordinarias y Recargos",
                            "vacaciones_incapacidades_y_licencias": "Vacaciones, Incapacidades y Licencias",
                            "ingresos_adicionales": "Ingresos Adicionales",
                            "retenciones_y_deducciones": "Retenciones y Deducciones",
                            "total_neto_a_pagar_al_empleado": "Total Neto a Pagar al Empleado"
                        };
                        for (let clave in data) {
                            let claveFormateada = clavesMapping[clave] || clave;
                            itemNomina += `
                                <span class="details-nom-item" href="#">
                                    <strong>
                                        ${claveFormateada} 
                                        <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 0 24 24" fill="none">
                                            <path d="M14.5 12C14.5 13.3807 13.3807 14.5 12 14.5C10.6193 14.5 9.5 13.3807 9.5 12C9.5 10.6193 10.6193 9.5 12 9.5C13.3807 9.5 14.5 10.6193 14.5 12Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"/>
                                            <path d="M19 11.1415C18.6749 11.0944 18.341 11.0586 18 11.0347M6 12.9653C5.65897 12.9415 5.32511 12.9056 5 12.8585" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="round"/>
                                            <path d="M12 4.5C10.6675 5.12236 8.91707 5.5 7 5.5C5.93408 5.5 4.5 5.5 2 4.5V19.5C4.5 20.5 5.93408 20.5 7 20.5C8.91707 20.5 10.6675 20.1224 12 19.5C13.3325 18.8776 15.0829 18.5 17 18.5C20 18.5 22 19.5 22 19.5V4.5C22 4.5 20 3.5 17 3.5C15.0829 3.5 13.3325 3.87764 12 4.5Z" stroke="#000000" stroke-width="1.5"/>
                                        </svg>
                                    </strong> 
                                    $${data[clave]}
                                </span>
                            `;
                        }

                        const menuInfoNomina = $('<div class="menu-info-nomina"></div>').html(
                            itemNomina);
                        $(`.modal-facturar__item-cont[data-idnomina="${idNomina}"]`).append(
                            menuInfoNomina);
                    }
                },
                error: function(error) {
                    Swal.fire({
                        type: 'error',
                        title: 'Hubo un problema',
                        text: 'Ha ocurrido un error al obtener los detalles de la nomina',
                    });
                }
            });
        }
    }
    const resultadosEmision = () => {
        if (items.length > 0) {
            let resultsEmision = items.map((item, index) => `
                    <article class="modal-facturar__item-cont--error">   
                        <div class="modal-emision__content--item">
                            <svg viewBox="0 0 24 24" width="26" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M9.29289 1.29289C9.48043 1.10536 9.73478 1 10 1H18C19.6569 1 21 2.34315 21 4V20C21 21.6569 19.6569 23 18 23H6C4.34315 23 3 21.6569 3 20V8C3 7.73478 3.10536 7.48043 3.29289 7.29289L9.29289 1.29289ZM18 3H11V8C11 8.55228 10.5523 9 10 9H5V20C5 20.5523 5.44772 21 6 21H18C18.5523 21 19 20.5523 19 20V4C19 3.44772 18.5523 3 18 3ZM6.41421 7H9V4.41421L6.41421 7ZM7 13C7 12.4477 7.44772 12 8 12H16C16.5523 12 17 12.4477 17 13C17 13.5523 16.5523 14 16 14H8C7.44772 14 7 13.5523 7 13ZM7 17C7 16.4477 7.44772 16 8 16H16C16.5523 16 17 16.4477 17 17C17 17.5523 16.5523 18 16 18H8C7.44772 18 7 17.5523 7 17Z" fill="currentColor"></path> </g></svg>
                            <span>${item}</span>
                            <svg viewBox="0 0 20 20" width="28" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M10 18C5.58172 18 2 14.4183 2 10C2 5.58172 5.58172 2 10 2C14.4183 2 18 5.58172 18 10C18 14.4183 14.4183 18 10 18ZM10 6C9.44772 6 9 6.44772 9 7V11C9 11.5523 9.44772 12 10 12C10.5523 12 11 11.5523 11 11V7C11 6.44772 10.5523 6 10 6ZM10 15C10.5523 15 11 14.5523 11 14C11 13.4477 10.5523 13 10 13C9.44772 13 9 13.4477 9 14C9 14.5523 9.44772 15 10 15Z" fill="currentColor"></path> </g></svg>
                        </div>
                        <div class="modal-emision__bar"> </div>
                    </article>
                `).join('');

            let templateResultados = `
                <div class="modal fades" id="resultados-emision">
                    <div class="d-block m-auto" style="height: auto; background: #fff; border-radius: 10px 10px 26px 26px;">
                        <header class="modal-emision__header">
                            <h4>Proceso de emisión masiva</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"
                                title="Cerrar modal facturar en lote">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </header>
                        
                    
                        <section class="modal-emision__content">
                            ${resultsEmision}
                        </section>

                    <footer class="modal-emision__footer"> 
                        <span class="modal-emision__items-length">0 de ${items.length} nominas</span>
                        <div class="d-flex" style="gap: 15px;">
                            <button class="modal-emision__btn-emitir imprimir">
                                Enviar por correo
                            </button>
                            <button class="modal-emision__btn-emitir" id="detalles-emision">
                                Detalles de la emision
                            </button>
                        </div>
                    </footer>
                    </div>
                </div>`;

            $('body').append(templateResultados);

            $('#resultados-emision').modal('show');
        }
    }

    $(document).ready(function() {

        $(document).on('click', '.modal-emision__btn-emitir', function(e) {

            e.preventDefault();
            /*
            $('#emision-masiva').modal('hide');
            resultadosEmision();
            */
        });
    });


    $(document).ready(function() {

        $(document).on('click', '#detalles-emision', function(e) {
            e.preventDefault();

            $('#resultados-emision').modal('show');

            detallesEmision();
        });
    });

    const detallesEmision = () => {
        if (true) {

            let detailsEmision = dataEmitida.map((item, index) => `
                        <tr>
                            <td>${item.codigo}</td>
                            <td>${item.mensaje} ${Array.isArray(item.data) ? item.data.join(', ') : (typeof item.data === 'object' ? JSON.stringify(item.data) : item.data)}</td>
                        </tr>
                `).join('');

            let templateDetalles = `
                <div class="modal show" id="detalles-emision-modal">
                    <div class="d-block m-auto" style="height: auto; background: #fff; border-radius: 10px 10px 26px 26px; max-width: 63vw;">
                        <header class="modal-emision__header">
                            <h4>Detalles de la emision de nominas</h4>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"
                                title="Cerrar modal facturar en lote">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </header>
                    
                        <section class="modal-emision__content details">
                            
                            <h3>Nominas <span id="txt-process-success">emitidas</span>: <span style="color: #63ECBC;">${countEmitidas}</span> </h3> 
                            <h3>Nominas <span id="txt-process-error">por emitir</span>: <span style="color: #fe9a03;">${countRechazadas}</span> </h3> 

                        <div class="modal-emision__breadcrumbs"><h3>Revisa las nominas pendientes por emitir</h3></div>
                        <table class="modal-emision__table" >
                            <thead>
                                <tr>
                                    <th>Nomina</th>
                                    <th>Descripcion</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${detailsEmision}
                            </tbody>
                        </table>
                        </section>

                    <footer class="modal-emision__footer"> 
                        <span class="modal-emision__items-length">${countEmitidas} de ${items.length} nominas</span>
                        <button class="modal-emision__btn-emitir" id="" onclick="emisionMasiva()">
                                Nominas
                        </button>
                    </footer>
                    </div>
                </div>`;

            $('body').append(templateDetalles);

            $('#detalles-emision-modal').modal('show');

            $('#emision-masiva').modal('hide');

            if(isEmail){
                $('#txt-process-success').text('enviadas al correo');
                $('#txt-process-error').text('perdidas');
                isEmail = false;
            }

        }
    }

    function extractText(htmlString) {
        let tempDiv = document.createElement("div");
        tempDiv.innerHTML = htmlString;
        return tempDiv.textContent || tempDiv.innerText || "";
    }
</script>


{{-- Js del modal de acciones en loteo de facturas de venta --}}

<script>
    async function realizarPeticion(id, email = false) {

        isEmail = email;
        const url = (email) ? `{{ route('emitir-nomina.email') }}/${id}` + '?lote=true' : `{{ route('nomina.json') }}/${id}/1?lote=true`;
    
        //Para asignar el icono asociado a la respuesta de la promesa
        const iconStatus = document.querySelectorAll('#icon-status-bill');

        const setIconStatus = (svgStatus) => {
            iconStatus.forEach(icon => {
                icon.innerHTML = svgStatus;
            });
        };

        return new Promise((resolve, reject) => {

            setIconStatus(
                '<svg width="24" height="24" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><style>.spinner_OSmW{transform-origin:center;animation:spinner_T6mA .75s step-end infinite}@keyframes spinner_T6mA{8.3%{transform:rotate(30deg)}16.6%{transform:rotate(60deg)}25%{transform:rotate(90deg)}33.3%{transform:rotate(120deg)}41.6%{transform:rotate(150deg)}50%{transform:rotate(180deg)}58.3%{transform:rotate(210deg)}66.6%{transform:rotate(240deg)}75%{transform:rotate(270deg)}83.3%{transform:rotate(300deg)}91.6%{transform:rotate(330deg)}100%{transform:rotate(360deg)}}</style><g class="spinner_OSmW"><rect x="11" y="1" width="2" height="5" opacity=".14"/><rect x="11" y="1" width="2" height="5" transform="rotate(30 12 12)" opacity=".29"/><rect x="11" y="1" width="2" height="5" transform="rotate(60 12 12)" opacity=".43"/><rect x="11" y="1" width="2" height="5" transform="rotate(90 12 12)" opacity=".57"/><rect x="11" y="1" width="2" height="5" transform="rotate(120 12 12)" opacity=".71"/><rect x="11" y="1" width="2" height="5" transform="rotate(150 12 12)" opacity=".86"/><rect x="11" y="1" width="2" height="5" transform="rotate(180 12 12)"/></g></svg>'
            );

            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);

            xhr.onload = function() {
                if (xhr.status >= 200 && xhr.status < 300) {

                    const jsonResponse = JSON.parse(xhr.responseText);

                    if (jsonResponse.success) {
                        $('#' + id).removeClass('modal-facturar__item-cont');
                        $('#' + id).removeClass('modal-facturar__item-cont--error');

                        $('#' + id).addClass('modal-facturar__item-cont--ok ');
                        setIconStatus(
                            '<svg viewBox="0 0 24 24" fill="none" width="28" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM16.0303 8.96967C16.3232 9.26256 16.3232 9.73744 16.0303 10.0303L11.0303 15.0303C10.7374 15.3232 10.2626 15.3232 9.96967 15.0303L7.96967 13.0303C7.67678 12.7374 7.67678 12.2626 7.96967 11.9697C8.26256 11.6768 8.73744 11.6768 9.03033 11.9697L10.5 13.4393L12.7348 11.2045L14.9697 8.96967C15.2626 8.67678 15.7374 8.67678 16.0303 8.96967Z" fill="currentColor"></path> </g></svg>'
                        );
                        valImprimir.push(jsonResponse.codigo);
                        countEmitidas++;
                    } else {
                        $('#' + id).removeClass('modal-facturar__item-cont');
                        $('#' + id).addClass('modal-facturar__item-cont--error');
                        setIconStatus(
                            '<svg viewBox="0 0 24 24" fill="none" width="28" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracurrentColorerCarrier" stroke-linecurrentcap="round" stroke-linejoin="round"></g><g id="SVGRepo_icurrentColoronCarrier"> <recurrentct width="24" height="24" fill="white"></recurrentct> <path fill-rule="evenodd" currentclip-rule="evenodd" d="M2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12ZM11 13C11 13.5523 11.4477 14 12 14C12.5523 14 13 13.5523 13 13V8C13 7.44772 12.5523 7 12 7C11.4477 7 11 7.44772 11 8V13ZM13 15.9888C13 15.4365 12.5523 14.9888 12 14.9888C11.4477 14.9888 11 15.4365 11 15.9888V16C11 16.5523 11.4477 17 12 17C12.5523 17 13 16.5523 13 16V15.9888Z" fill="currentColor"></path> </g></svg>'
                        )
                        countRechazadas++;
                        //valImprimir.push(jsonResponse.codigo);
                        dataEmitida.push({
                            codigo: jsonResponse.ref ?? '',
                            mensaje: (jsonResponse.mesagge ?? ''),
                            data: jsonResponse.data
                        });
                    }

                    $('#btn-emitir').addClass('disabled');

                    resolve(jsonResponse);
                } else {
                    reject(new Error(`Error en la solicitud para ID ${id}. Estado ${xhr.status}`));
                }
            };

            xhr.onerror = function() {
                reject(new Error(`Error de red para ID ${id}`));
            };

            xhr.send();
        });
    }

    async function inicializarArrayConPeticiones(ids, email) {
        const resultados = [];

        const totalPeticiones = ids.length;
    
        for (const [index, id] of ids.entries()) {
            try {
                const respuesta = await realizarPeticion(id, email);

                const porcentaje = ((index + 1) / totalPeticiones) * 100;


                //Para añadir el numero del porcentaje al progress bar
                const porcentajeElement = document.getElementById('percentage-number');
                porcentajeElement.textContent = `${porcentaje}%`;

                //Para añadir el porcentaje al progress bar
                const progressBar = document.querySelector('#bar-progress-emitir');
                const partyPopper = document.getElementById('party-popper');

                progressBar.style.width = `${porcentaje}%`;
                progressBar.dataset.percentage = porcentaje;

                if (porcentaje === 100) {
                    if(email){
                        porcentajeElement.textContent = 'Nominas enviadas al correo de los empleados';
                    }else{
                        porcentajeElement.textContent = 'Nominas procesadas correctamente';
                    }
                    partyPopper.style.display = 'inline'; // Show the image
                } else {
                    porcentajeElement.textContent = `${porcentaje}%`;
                    partyPopper.style.display = 'none'; // Show the image
                }

                resultados.push(respuesta);
                console.log(`Solicitud exitosa para ID ${id}`);
            } catch (error) {
                console.error(error.message);
            }
        }

        return resultados;
    }


    function emitirLote(email = false) {
        var ids = [];
        valImprimir = [];
        dataEmitida = [];
        countRechazadas = 0;
        countEmitidas = 0;

        $('#btn-imprimir-all').css('display', 'none');
        $('#cont-progress-bar').fadeOut(500, function() {
            $(this).fadeIn(500);
        });

        $('.code-document').each(function(e) {
            ids.push($(this).attr('code'));
        });

        inicializarArrayConPeticiones(ids, email)
            .then(resultados => {
                $('#boton-after-emitir').css('display', 'flex');
                $('#btn-emitir').css('display', 'none');
                console.log('Todas las solicitudes completadas:', resultados);
                $('#btn-emitir').removeClass('disabled');
            })
            .catch(error => {
                console.error('Error general:', error);
                $('#btn-emitir').removeClass('disabled');
            });


    }
</script>
