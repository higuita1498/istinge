<cac:{{$node}}>
<cbc:AdditionalAccountID>@if($data['tipo_persona']!=null)@if($data['tipo_persona'] =='n'||$data['tipo_persona']==1){{2}}@elseif($data['tipo_persona']=='j'|| $data['tipo_persona']==2){{1}}@endif
@else{{2}}@endif</cbc:AdditionalAccountID> {{--TIPO DE ORGANIZACION JURIDICA:1 PERSONAL:2--}}
<cac:Party>
        @if ($data['tipo_persona'] =='n' || $data['tipo_persona'] == 1)
        <cac:PartyIdentification>
            <cbc:ID  schemeName="{{ \App\Contacto::codigo_ident_static($data['tip_iden'])->codigo_dian }}">{{ $data['nit'] }}</cbc:ID>
        </cac:PartyIdentification>
            @endif
        <cac:PartyName>
            <cbc:Name>{{$data['nombre']}} @if(isset($Cliente)){{$data['apellido1']}} {{$data['apellido2']}} @endif</cbc:Name>
        </cac:PartyName>
        @isset($Empresa)
        <cac:PhysicalLocation>
        <cac:Address>
        <cbc:ID>{{ Auth::user()->empresaObj->municipio()->codigo_completo }}</cbc:ID> {{-- CODIGO DEL MUNICIPIO --}}
        <cbc:CityName>{{ Auth::user()->empresaObj->municipio()->nombre }}</cbc:CityName> {{-- nombre de el municipio --}}
        <cbc:PostalZone>{{ Auth::user()->empresaObj->cod_postal }}</cbc:PostalZone>{{-- Código postal --}}
        <cbc:CountrySubentity>{{ Auth::user()->empresaObj->departamento()->nombre }}</cbc:CountrySubentity> {{-- nombre del departamento --}}
        <cbc:CountrySubentityCode>{{ Auth::user()->empresaObj->departamento()->codigo }}</cbc:CountrySubentityCode> {{-- codigo del departamento --}}
        <cac:AddressLine>
        <cbc:Line>{{$data['direccion']}}</cbc:Line>
    </cac:AddressLine>
    <cac:Country>
    <cbc:IdentificationCode>{{ Auth::user()->empresaObj->fk_idpais }}</cbc:IdentificationCode> {{-- codigo del pais --}}
    <cbc:Name languageID="es">{{ Auth::user()->empresaObj->pais()->nombre }}</cbc:Name> {{-- lenguaje y codigo del pais--}}
</cac:Country>
</cac:Address>
</cac:PhysicalLocation>
@endisset
@isset($data['empresa'])
<cac:PhysicalLocation>
<cac:Address>
<cbc:ID>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->codigo_completo }}</cbc:ID> {{-- CODIGO DEL MUNICIPIO --}}
<cbc:CityName>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->nombre }}</cbc:CityName> {{-- nombre de el municipio --}}
<cbc:PostalZone>@if($data['cod_postal']){{$data['cod_postal']}}@else
{{\App\Contacto::municipio_static($data['fk_idmunicipio'])->codigo_completo}}@endif</cbc:PostalZone>{{-- Código postal --}}
<cbc:CountrySubentity>{{ \App\Contacto::departamento_static($data['fk_iddepartamento'])->nombre }}</cbc:CountrySubentity> {{-- nombre del departamento --}}
<cbc:CountrySubentityCode>{{ \App\Contacto::departamento_static($data['fk_iddepartamento'])->codigo }}</cbc:CountrySubentityCode> {{-- codigo del departamento --}}
<cac:AddressLine>
<cbc:Line>{{$data['direccion']}}</cbc:Line>
</cac:AddressLine>
<cac:Country>
<cbc:IdentificationCode>{{ $data['fk_idpais'] }}</cbc:IdentificationCode> {{-- codigo del pais --}}
<cbc:Name languageID="es">{{ \App\Contacto::pais_static($data['fk_idpais'])->nombre }}</cbc:Name> {{-- lenguaje y codigo del pais--}}
</cac:Country>
</cac:Address>
</cac:PhysicalLocation>
@endisset
<cac:PartyTaxScheme>
<cbc:RegistrationName>{{$data['nombre']}} @if(isset($Cliente)){{$data['apellido1']}} {{$data['apellido2']}} @endif</cbc:RegistrationName>
<cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" @if($data['dv'] != null) schemeID="{{$data['dv']}}" @endif schemeName="{{ \App\Contacto::codigo_ident_static($data['tip_iden'])->codigo_dian }}">{{ $data['nit'] }}</cbc:CompanyID>

@if(isset($Empresa))
<?php $contresp = count($responsabilidades_empresa); $cont = 0; ?>
<cbc:TaxLevelCode listName="{{-- TIPO DE REGIMEN SIMPLE 04 O ORDINARIO 05 --}}05">@foreach($responsabilidades_empresa as $responsabilidad){{-- TIPO DE RESPONSABILIDAD--}}{{ $responsabilidad->codigo }}<?php $cont = $cont +1;
if($contresp != $cont) {echo ";";}?>@endforeach</cbc:TaxLevelCode>
@endif

@if(isset($Cliente))
@if($data['responsableiva'] == 1)
<cbc:TaxLevelCode listName="{{-- TIPO DE REGIMEN SIMPLE 04 O ORDINARIO 05 --}}05">R-99-PN</cbc:TaxLevelCode>
@elseif($data['responsableiva'] == 2)
<cbc:TaxLevelCode listName="{{-- TIPO DE REGIMEN SIMPLE 04 O ORDINARIO 05 --}}05">R-99-PN</cbc:TaxLevelCode>
@endif
@endif
<cac:RegistrationAddress>

<cbc:ID>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->codigo_completo }}</cbc:ID> {{-- CODIGO DE LA MUNICIPALIDAD --}}
<cbc:CityName>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->nombre }}</cbc:CityName> {{-- nombre de el municipio --}}
<cbc:PostalZone>@if(($data['cod_postal'])){{$data['cod_postal']}}@else
{{\App\Contacto::municipio_static($data['fk_idmunicipio'])->codigo_completo}}@endif</cbc:PostalZone>{{-- Código postal --}}
<cbc:CountrySubentity>{{ \App\Contacto::departamento_static($data['fk_iddepartamento'])->nombre }}</cbc:CountrySubentity> {{-- nombre del departamento --}}
<cbc:CountrySubentityCode>{{ \App\Contacto::departamento_static($data['fk_iddepartamento'])->codigo }}</cbc:CountrySubentityCode> {{-- codigo del departamento --}}
<cac:AddressLine>
<cbc:Line>{{$data['direccion']}}</cbc:Line>
</cac:AddressLine>
<cac:Country>
<cbc:IdentificationCode>{{ $data['fk_idpais'] }}</cbc:IdentificationCode> {{-- codigo del pais --}}
<cbc:Name languageID="es">{{ \App\Contacto::pais_static($data['fk_idpais'])->nombre }}</cbc:Name> {{-- lenguaje y codigo del pais--}}
</cac:Country>
</cac:RegistrationAddress>
<cac:TaxScheme>
<cbc:ID>01</cbc:ID>
<cbc:Name>IVA</cbc:Name>
</cac:TaxScheme>
</cac:PartyTaxScheme>
<cac:PartyLegalEntity>
<cbc:RegistrationName>{{$data['nombre']}} @if(isset($Cliente)){{$data['apellido1']}} {{$data['apellido2']}}@endif</cbc:RegistrationName>
<cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" @if($data['dv'] != null) schemeID="{{$data['dv']}}"  @endif schemeName="{{ \App\Contacto::codigo_ident_static($data['tip_iden'])->codigo_dian }}">{{ $data['nit'] }}</cbc:CompanyID>

@if(isset($nc))
@if(auth()->user()->empresa != 22 && auth()->user()->empresa != 80 && auth()->user()->empresa != 50 && auth()->user()->empresa != 77 && auth()->user()->empresa != 14 && auth()->user()->empresa != 101 && auth()->user()->empresa != 71 && auth()->user()->empresa != 70 && auth()->user()->empresa != 73 && auth()->user()->empresa != 72 && auth()->user()->empresa != 65 && auth()->user()->empresa != 64
&& auth()->user()->empresa != 63 && auth()->user()->empresa != 62 && auth()->user()->empresa != 64 && auth()->user()->empresa != 52 && auth()->user()->empresa != 274
&& auth()->user()->empresa != 26 && auth()->user()->empresa != 15 && auth()->user()->empresa != 25 && auth()->user()->empresaObj->id != 12 && auth()->user()->empresaObj->id != 255 
&& auth()->user()->empresaObj->id != 90 && auth()->user()->empresaObj->id != 111 && auth()->user()->empresaObj->id != 42 && auth()->user()->empresaObj->id != 102
&& auth()->user()->empresaObj->id != 1 && auth()->user()->empresaObj->id != 112 && auth()->user()->empresaObj->id != 113 && auth()->user()->empresaObj->id != 200
&& auth()->user()->empresaObj->id != 37 && auth()->user()->empresaObj->id != 61 && auth()->user()->empresaObj->id != 216 && auth()->user()->empresaObj->id != 270 && auth()->user()->empresaObj->id != 4  && auth()->user()->empresaObj->id != 240 && auth()->user()->empresaObj->id != 43)
<cac:CorporateRegistrationScheme>
@isset($Empresa)
<cbc:ID></cbc:ID>
@endisset
<cbc:Name>{{$data['nit']}}</cbc:Name>
</cac:CorporateRegistrationScheme>
@endif
@else
<cac:CorporateRegistrationScheme>
@isset($Empresa)
<cbc:ID>{{$ResolucionNumeracion->prefijo}}</cbc:ID>
@endisset
<cbc:Name>{{$data['nit']}}</cbc:Name>
</cac:CorporateRegistrationScheme>
@endif

</cac:PartyLegalEntity>
<cac:Contact>
@if(isset($data['telefono1']))
<cbc:Telephone>{{$data['telefono1']}}</cbc:Telephone>
@elseif(isset($data['telefono']))
<cbc:Telephone>{{$data['telefono']}}</cbc:Telephone>
@elseif(isset($data['celular']))
<cbc:Telephone>{{$data['celular']}}</cbc:Telephone>
@endif

@php 
$cont = 0;  
    if(is_array($emails))
    {
        $max = count($emails);
    }
    else{ $max = 1; }
@endphp
<cbc:ElectronicMail>@if(isset($Cliente) && $max > 1)@foreach($emails as $email => $key)<?php $cont++;?>{{$key}}{{$cont < $max ?';':''}}@endforeach @else{{$data['email']}}@endif</cbc:ElectronicMail>

</cac:Contact>
</cac:Party>
</cac:{{$node}}>
