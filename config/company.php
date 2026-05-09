<?php

/*
|--------------------------------------------------------------------------
| Datos de empresa por unidad de negocio
|--------------------------------------------------------------------------
|
| Cada unidad de negocio tiene su propia razón social. Estos datos se
| inyectan en documentos legales (contratos laborales, etc.) según la
| `business_unit` del empleado.
|
| Para actualizar datos reales de una unidad: editá la entrada
| correspondiente en `units`. Los datos marcados como [DUMMY] son
| placeholders hasta que el negocio confirme la información real.
|
*/

return [
    'units' => [
        'Jade' => [
            'legal_name' => 'COMERCIO Y TURISMO LA CAÑADA, S. DE R. L.',
            'commercial_name' => 'CAFÉ JADE RESTAURANTE',
            'legal_representative' => 'DAVID MOISES MORALES MAYORGA',
            'address' => 'PROLONGACIÓN AV. HIDALGO ESQ. 5TA PONIENTE NORTE N.2, COL. LA CAÑADA, PALENQUE, CHIAPAS.',
            'rfc' => 'CTC161110145',
            'business_object' => 'las actividades relativas a la producción, comercialización y distribución de alimentos y bebidas.',
            'sign_city' => 'PALENQUE, CHIAPAS',
        ],

        'Jade Orgánico' => [
            'legal_name' => '[DUMMY] RAZÓN SOCIAL JADE ORGÁNICO, S. DE R. L.',
            'commercial_name' => 'JADE ORGÁNICO',
            'legal_representative' => '[DUMMY] APODERADO LEGAL POR DEFINIR',
            'address' => '[DUMMY] DOMICILIO POR DEFINIR, PALENQUE, CHIAPAS.',
            'rfc' => '[DUMMY] RFC POR DEFINIR',
            'business_object' => 'las actividades relativas a la producción, comercialización y distribución de alimentos y bebidas orgánicos.',
            'sign_city' => 'PALENQUE, CHIAPAS',
        ],

        'KIN' => [
            'legal_name' => '[DUMMY] RAZÓN SOCIAL KIN, S. DE R. L.',
            'commercial_name' => 'KIN',
            'legal_representative' => '[DUMMY] APODERADO LEGAL POR DEFINIR',
            'address' => '[DUMMY] DOMICILIO POR DEFINIR, PALENQUE, CHIAPAS.',
            'rfc' => '[DUMMY] RFC POR DEFINIR',
            'business_object' => 'las actividades relativas a la producción, comercialización y distribución de alimentos y bebidas.',
            'sign_city' => 'PALENQUE, CHIAPAS',
        ],
    ],
];
