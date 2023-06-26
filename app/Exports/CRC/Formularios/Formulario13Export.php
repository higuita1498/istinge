<?php

namespace App\Exports\CRC\Formularios;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class Formulario13Export implements FromCollection, WithHeadings, ShouldAutoSize, WithMapping, WithStrictNullComparison
{
    private const ELECTRONIC_INVOICE = 2;

    private const INVOICE_CLOSED_STATUS = 0;

    private ?string $initialDate;

    private ?string $finalDate;

    public function __construct(?string $initialDate, ?string $finalDate)
    {
        $this->initialDate = $initialDate;
        $this->finalDate = $finalDate;
    }

    public function collection(): Collection
    {
        /*
         * Query creada:
         SELECT
            YEAR(co.created_at) as yea,
            QUARTER(co.created_at) as qua,
            m.codigo_completo,
            con.estrato,
            1,
            pv.download,
            pv.upload,
            co.tecnologia,
            co.state,
            COUNT(DISTINCT co.id),
            pv.price * COUNT(con.id),
            0
        FROM factura f
            JOIN numeraciones_facturas nf ON (f.numeracion = nf.id)
            JOIN contracts co ON (co.id = f.contrato_id)
            JOIN planes_velocidad pv ON (pv.id = co.plan_id)
            JOIN contactos con ON (con.id = co.client_id)
            JOIN municipios m ON (con.fk_idmunicipio = m.id)
        WHERE
            nf.tipo = 2
            AND f.estatus = 0
            AND co.created_at IS NOT NULL
        GROUP BY
            yea,
            qua,
            m.codigo_completo,
            con.estrato,
            pv.download,
            pv.upload,
            co.tecnologia,
            co.state
        ORDER BY yea DESC, qua DESC;
        */
        $query = DB::table('factura')
            ->select('municipios.codigo_completo', 'contactos.estrato', 'planes_velocidad.download', 'planes_velocidad.upload', 'contracts.tecnologia', 'contracts.state')
            ->selectRaw('YEAR(contracts.created_at) as year')
            ->selectRaw('QUARTER(contracts.created_at) as quarter')
            ->selectRaw('1 as id_servicio_paquete')
            // De todas las facturas, contar únicamente los contratos
            ->selectRaw('COUNT(DISTINCT contracts.id) as cantidad_lineas_accesos')
            ->selectRaw('planes_velocidad.price * COUNT(contactos.id) as valor_facturado_o_cobrado')
            ->selectRaw('0 as otros_valores_facturados')
            ->join('numeraciones_facturas', 'numeraciones_facturas.id', '=', 'factura.numeracion')
            ->join('contracts', 'contracts.id', '=', 'factura.contrato_id')
            ->join('planes_velocidad', 'planes_velocidad.id', '=', 'contracts.plan_id')
            ->join('contactos', 'contactos.id', '=', 'contracts.client_id')
            ->join('municipios', 'municipios.id', '=', 'contactos.fk_idmunicipio')
            ->where('numeraciones_facturas.tipo', Formulario13Export::ELECTRONIC_INVOICE)
            ->where('factura.estatus', Formulario13Export::INVOICE_CLOSED_STATUS)
            ->whereNotNull('contracts.created_at')
            ->groupBy('year', 'quarter', 'municipios.codigo_completo', 'contactos.estrato', 'planes_velocidad.download', 'planes_velocidad.upload', 'contracts.tecnologia', 'contracts.state');

        if ($this->initialDate) {
            $query->where('contracts.created_at', '>=', $this->initialDate);
        }

        if ($this->finalDate) {
            $query->where('contracts.created_at', '<=', $this->finalDate);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return ['ANNO', 'TRIMESTRE', 'ID_MUNICIPIO', 'ID_SEGMENTO', 'ID_SERVICIO_PAQUETE', 'VELOCIDAD_EFECTIVA_DOWNSTREAM', 'VELOCIDAD_EFECTIVA_UPSTREAM', 'ID_TECNOLOGIA_ACCESO', 'ID_ESTADO', 'CANTIDAD_LINEAS ACCESOS', 'VALOR_FACTURADO_O_COBRADO', 'OTROS_VALORES_FACTURADOS'];
    }

    public function map($row): array
    {
        return [$row->year, $row->quarter, $row->codigo_completo, $this->getIdSegmentoFromEstrato($row->estrato), $row->id_servicio_paquete, $this->parseVelocity($row->download), $this->parseVelocity($row->upload), $this->getTecnologiaAccesoFromTecnologia($row->tecnologia), $this->getIdEstadoFromEstado($row->state), $row->cantidad_lineas_accesos, $row->valor_facturado_o_cobrado, $row->otros_valores_facturados];
    }

    private function getIdSegmentoFromEstrato(?string $estrato): int
    {
        // Estos códigos son únicos para este reporte.
        switch ($estrato) {
            case '1':
                // Estrato 1
                return 101;
            case '2':
                // Estrato 2
                return 102;
            case '3':
                // Estrato 3
                return 103;
            case '4':
                // Estrato 4
                return 104;
            case '5':
                // Estrato 5
                return 105;
            case '6':
                // Estrato 6
                return 106;
            case '0':
            case '':
            case null:
            default:
                // Sin estratificar
                return 108;
        }
    }

    private function parseVelocity(?string $velocity): int
    {
        if (is_null($velocity)) {
            return 0;
        }

        // Por como está guardada la información en la base de datos, como
        // `4M` o `4KB`, debería ser solo un número.
        return (int) preg_replace('/[a-zA-Z]/', '', $velocity);
    }

    private function getTecnologiaAccesoFromTecnologia(?int $tecnologia): int
    {
        // Estos códigos son únicos para este reporte.
        switch ($tecnologia) {
            case 1:
                // Tecnología: Fiber to the Home (FTTH)
                return 108;
            default:
                // Otras tecnologías inalámbricas
                return 114;
        }
    }

    private function getIdEstadoFromEstado(?string $estado): int
    {
        // Igualmente, por como está guardada la información en la base de datos,
        // requiere ser cambiado.
        switch ($estado) {
            case 'enabled':
                return 1;
            default:
                return 0;
        }
    }
}
