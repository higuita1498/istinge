<cac:{{$node}}>
<cbc:AdditionalAccountID>@if($data['tipo_persona']!=null)@if($data['tipo_persona'] =='n'||$data['tipo_persona']==1){{2}}@elseif($data['tipo_persona']=='j'|| $data['tipo_persona']==2){{1}}@endif
@else{{2}}@endif</cbc:AdditionalAccountID> {{--TIPO DE ORGANIZACION JURIDICA:1 PERSONAL:2--}}
<cac:Party>
        {{--@if ($Empresa->tipo_persona == 'n')
            <cac:PartyIdentification>
               <cbc:ID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{$user->company->dv}}" schemeName="{{$user->company->type_document_identification->code}}">{{$user->company->identification_number}}</cbc:ID>
            </cac:PartyIdentification>
            @endif--}}
            <cac:PartyName>
            <cbc:Name>{{$data['nombre']}}</cbc:Name>
        </cac:PartyName>
        @isset($Empresa)
        <cac:PhysicalLocation>
        <cac:Address>
        <cbc:ID>{{ Auth::user()->empresa()->municipio()->codigo_completo }}</cbc:ID> {{-- CODIGO DEL MUNICIPIO --}}
        <cbc:CityName>{{ Auth::user()->empresa()->municipio()->nombre }}</cbc:CityName> {{-- nombre de el municipio --}}
        <cbc:PostalZone>{{ Auth::user()->empresa()->cod_postal }}</cbc:PostalZone>{{-- Código postal --}}
        <cbc:CountrySubentity>{{ Auth::user()->empresa()->departamento()->nombre }}</cbc:CountrySubentity> {{-- nombre del departamento --}}
        <cbc:CountrySubentityCode>{{ Auth::user()->empresa()->departamento()->codigo }}</cbc:CountrySubentityCode> {{-- codigo del departamento --}}
        <cac:AddressLine>
        <cbc:Line>{{$data['direccion']}}</cbc:Line>
    </cac:AddressLine>
    <cac:Country>
    <cbc:IdentificationCode>{{ Auth::user()->empresa()->fk_idpais }}</cbc:IdentificationCode> {{-- codigo del pais --}}
    <cbc:Name languageID="es">{{ Auth::user()->empresa()->pais()->nombre }}</cbc:Name> {{-- lenguaje y codigo del pais--}}
</cac:Country>
</cac:Address>
</cac:PhysicalLocation>
@endisset
@isset($data['empresa'])
<cac:PhysicalLocation>
<cac:Address>
<cbc:ID>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->codigo_completo }}</cbc:ID> {{-- CODIGO DEL MUNICIPIO --}}
<cbc:CityName>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->nombre }}</cbc:CityName> {{-- nombre de el municipio --}}
<cbc:PostalZone>@if(count($data['cod_postal']) > 0){{$data['cod_postal']}}@else
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
<cbc:RegistrationName>{{$data['nombre']}}</cbc:RegistrationName>
<cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{ $data['dv'] }}" schemeName="{{ \App\Contacto::codigo_ident_static($data['tip_iden'])->codigo_dian }}">{{ $data['nit'] }}</cbc:CompanyID>

@if(isset($Empresa))
<?php $contresp = count($responsabilidades_empresa); $cont = 0; ?>
<cbc:TaxLevelCode listName="{{-- TIPO DE REGIMEN SIMPLE 04 O ORDINARIO 05 --}}05">@foreach($responsabilidades_empresa as $responsabilidad){{-- TIPO DE RESPONSABILIDAD--}}{{ $responsabilidad->codigo }}<?php $cont = $cont +1;
if($contresp != $cont) {echo ";";}?>@endforeach</cbc:TaxLevelCode>
@endif

@if(isset($Cliente))
@if($data['responsableiva'] == 1)
<cbc:TaxLevelCode listName="{{-- TIPO DE REGIMEN SIMPLE 04 O ORDINARIO 05 --}}05">O-48</cbc:TaxLevelCode>
@elseif($data['responsableiva'] == 2)
<cbc:TaxLevelCode listName="{{-- TIPO DE REGIMEN SIMPLE 04 O ORDINARIO 05 --}}05">O-49</cbc:TaxLevelCode>
@endif
@endif
<cac:RegistrationAddress>

<cbc:ID>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->codigo_completo }}</cbc:ID> {{-- CODIGO DE LA MUNICIPALIDAD --}}
<cbc:CityName>{{ \App\Contacto::municipio_static($data['fk_idmunicipio'])->nombre }}</cbc:CityName> {{-- nombre de el municipio --}}
<cbc:PostalZone>@if(count($data['cod_postal']) > 0){{$data['cod_postal']}}@else
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
<cbc:RegistrationName>{{$data['nombre']}}</cbc:RegistrationName>
<cbc:CompanyID schemeAgencyID="195" schemeAgencyName="CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)" schemeID="{{ $data['dv'] }}" schemeName="{{ \App\Contacto::codigo_ident_static($data['tip_iden'])->codigo_dian }}">{{ $data['nit'] }}</cbc:CompanyID>
<cac:CorporateRegistrationScheme>
@isset($Empresa)
<cbc:ID>{{$ResolucionNumeracion->prefijo}}</cbc:ID>
@endisset
<cbc:Name>{{$data['nit']}}</cbc:Name>
</cac:CorporateRegistrationScheme>
</cac:PartyLegalEntity>
<cac:Contact>
@if(isset($data['telefono1']))
<cbc:Telephone>{{$data['telefono1']}}</cbc:Telephone>
@else
<cbc:Telephone>{{$data['telefono']}}</cbc:Telephone>
@endif
<cbc:ElectronicMail>{{$data['email']}}</cbc:ElectronicMail>
</cac:Contact>
</cac:Party>
</cac:{{$node}}>
