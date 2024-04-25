<?php

namespace App\Http\Controllers;

use App\Instance;
use App\Services\WapiService;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;

class InstanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instance = Instance::where('company_id', auth()->user()->empresa)->first();
        return response()->json($instance);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, WapiService $wapiService)
    {
        $addr = url('');
        $validated = $request->validate([
            'instance_id' => 'required|regex:/[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}/'
        ]);

        try {
            $response = $wapiService->getInstance($validated['instance_id']);
        } catch (ClientException $e) {
            if($e->getResponse()->getStatusCode() === 404) {
                return back()->withErrors([
                    'instance_id' => 'Esta instancia no existe, valida el identificador con tu proveedor.'
                ])->withInput($request->input());
            }
        }
        $responseInstance = (object) json_decode($response)->data;
        try {

            $instance = Instance::create([
                'uuid' => $responseInstance->uuid,
                'company_id' => auth()->user()->empresa,
                'addr' => $addr,
                'api_key' => $responseInstance->apiKey,
                'uuid_whatsapp' => $responseInstance->uuidWhatsapp,
                'status' => $responseInstance->status
            ]);
            return back()->with([
                'instance' => $instance,
                'message' => 'Instancia registrada correctamente.'
            ]);
        } catch (Exception $err) {
            return back()->withErrors([
                'instance_id' => 'Esta instancia ya ha sido registrada.'
            ])->withInput($request->input());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, WapiService $wapiService)
    {
        $instance = Instance::where('uuid', $id)->first();

        if(!$instance) {
            return response()->json([
                'status' => 'error',
                'data' => [
                    'error' => 'Instancia no encontrada.'
                ]
            ]);
        }

        try {
            $response = $wapiService->getInstance($instance->uuid);
        } catch (ClientException $e) {
            if($e->getResponse()->getStatusCode() === 404) {
                return back()->withErrors([
                    'instance_id' => 'Esta instancia no existe, valida el identificador con tu proveedor.'
                ])->withInput($request->input());
            }
        }
        $responseInstance = (object) json_decode($response)->data;

        if($responseInstance->status === 'PAIRED') {
            $instance->status = 'PAIRED';
            $instance->save();
            return response()->json([
                'status' => 'success',
                'data' => [
                    'message' => 'Instancia emparejada correctamente.'
                ]
            ]);
        }
        $instance->status = 'UNPAIRED';
        $instance->save();
        return response()->json([
            'status' => 'success',
            'data' => [
                'message' => 'Instancia actualizada correctamente.'
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function pair($id, WapiService $wapiService)
    {
        $instance = Instance::where("id", $id)->first();
        if(!$instance) {
            return response()->json([
                'status' => 'error',
                'data' => [
                    'error' => 'Instancia no encontrada.'
                ]
            ]);
        }

        $session = $wapiService->initSession($instance->uuid_whatsapp, $instance->api_key);
        $session = json_decode($session);
        if($session->status === 'error') {
            return response()->json([
                'status' => 'error',
                'data' => [
                    'error' => 'Error al iniciar sesión.'
                ]
            ]);
        }
        return response()->json([
            'status' => 'success',
            'data' => [
                'message' => 'Sesión iniciada correctamente.'
            ]
        ]);
    }
}
