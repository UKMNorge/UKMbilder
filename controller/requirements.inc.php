<?php

require_once('UKMconfig.inc.php');

// Mangler konstanten (defineres i UKMconfig.inc.php)
if( !defined('UKM_BILDER_SYNC_FOLDER') ) {
    UKMbilder::getFlashbag()->error(
        'Feil-konfigurasjon av serveren gjør at opplasting av bilder ikke er mulig. '.
        '<a href="mailto:support@ukm.no?subject=UKMbilder mangler konstanten UKM_BILDER_SYNC_FOLDER i config.">Kontakt UKM Norge!</a>'
    );
}
// Syncfolder er definert uten trailing slash
elseif( substr(UKM_BILDER_SYNC_FOLDER, -1) !== '/') {
    UKMbilder::getFlashbag()->error(
        'Feil-konfigurasjon av serveren gjør at opplasting av bilder ikke er mulig. '.
        '<a href="mailto:support@ukm.no?subject=UKMbilder syncfolder mangler trailing slash">Kontakt UKM Norge!</a>'
    );
}
// Mappa finnes ikke
elseif( !file_exists( UKM_BILDER_SYNC_FOLDER ) ) {
    UKMbilder::getFlashbag()->error(
        'Feil-konfigurasjon av serveren gjør at opplasting av bilder ikke er mulig. '.
        '<a href="mailto:support@ukm.no?subject=UKMbilder mangler syncfolder">Kontakt UKM Norge!</a>'
    );
}
// Mappa er ikke skrivbar
elseif( !is_writable(UKM_BILDER_SYNC_FOLDER) ) {
    UKMbilder::getFlashbag()->error(
        'Feil-konfigurasjon av serveren gjør at opplasting av bilder ikke er mulig. '.
        '<a href="mailto:support@ukm.no?subject=UKMbilder mangler rettigheter til syncfolder">Kontakt UKM Norge!</a>'
    );  
}