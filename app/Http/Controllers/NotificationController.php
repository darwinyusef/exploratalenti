<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public static function ms(
        $type,
        $status,
        $data = null,
        $ms = null,
        $action = null
    ) {
        $msFin = self::validateMs($status, $type, $ms);

        if ($ms == null || !isset($ms)) {
            return $msFin == null ? $allMs[0] : $msFin;
        }

        return response()->json(
            [
                'type' => $type,
                'ms' => $msFin,
                'data' => $data,
            ],
            $status
        );
    }

    public static function validateMs($status = 201, $type = 'ok', $ms = null)
    {
        $allMs = [
            'Datos Listados correctamente',
            'Datos Editados correctamente',
            'Datos Creados correctamente',
            'Datos Borrados correctamente',
            'Error en la request',
            'No se ha encontrado la respuesta',
            'Información generada correctamente',
        ];

        if ($status == 201 && $type == 'ok') {
            return $ms == null ? $allMs[0] : $ms;
        }

        if ($status == 201 && $type == 'Edit') {
            return $ms == null ? $allMs[1] : $ms;
        }
        if ($status == 201 && $type == 'Create') {
            return $ms == null ? $allMs[2] : $ms;
        }
        if ($status == 201 && $type == 'Delete') {
            return $ms == null ? $allMs[3] : $ms;
        }

        if ($status == 400 && $type == 'error') {
            return $ms == null ? $allMs[4] : $ms;
        }

        if ($status == 404 && $type == 'no-found') {
            return $ms == null ? $allMs[5] : $ms;
        }
    }
}
