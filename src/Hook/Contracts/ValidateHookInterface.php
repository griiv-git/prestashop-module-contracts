<?php

namespace Griiv\Prestashop\Module\Contracts\Hook\Contracts;

/**
 * Contrat des hooks de validation de formulaire PrestaShop.
 *
 * Concerne les hooks dont le nom commence par « validate », par exemple
 * validateCustomerFormFields et validateCustomerAddressForm.
 *
 * Ces hooks reçoivent les instances de FormField créées par le module et
 * signalent leurs erreurs en mutant ces objets directement
 * (FormField::addError()), car le coeur matérialise les erreurs à partir
 * des objets eux-mêmes et non de la valeur de retour du hook.
 *
 * Aucun type de retour n'est imposé : le coeur de PrestaShop exploite
 * inégalement la valeur retournée selon les versions et les formulaires.
 */
interface ValidateHookInterface
{
    /**
     * @param array $params
     *
     * @return mixed
     */
    public function validate($params);
}
