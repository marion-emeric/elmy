<?php

namespace App\Service;

class StandardizePowerhouseService
{
    public function aggregateDataByDate(array $data, array $powerhouses, int $fromDate, int $toDate)
    {
        // TODO - step 1: Pour chaque Centrale vérifier qu'il y ait une ligne par tranche de 15 min sinon => prendre la valeur précédente et insérer une ligne correspondant à cette tranche horaire
        // => Pour Barnsley prendre la valeur et la diviser par 2 ($powerhouses[1]['step']/15)
        // => Pour Hounslow prendre la valeur et la diviser par 4 ($powerhouses[2]['step']/15)

        // TODO - step 2: Normaliser en parallèle les clés des tableaux ("start", "end", "power")

        // TODO - step 3: Additionner le "power" de chaque ligne de chaque tableau des centrales
    }
}
