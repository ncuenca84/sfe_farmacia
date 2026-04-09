<?php

return [
    'dir_base' => env('SFE_DIR_BASE', '/home/usuario/documentos'),
    'masteroffline_url' => env('SRI_MASTEROFFLINE_URL', 'http://localhost:8080/MasterOffline/ProcesarComprobanteElectronico?wsdl'),
    'timeout' => env('SRI_TIMEOUT', 600),
    'soap_cache' => env('SRI_SOAP_CACHE', 0),
];
