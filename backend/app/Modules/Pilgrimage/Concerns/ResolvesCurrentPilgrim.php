<?php

declare(strict_types=1);

namespace App\Modules\Pilgrimage\Concerns;

use App\Models\User;
use App\Modules\Pilgrimage\Models\Pilgrim;

/**
 * Trait — Résolution du Pilgrim courant avec mémorisation par requête.
 *
 * Élimine les 14 occurrences de `Pilgrim::query()->where('user_id', ...)->first()`
 * dupliquées dans les policies et controllers.
 *
 * La propriété `$resolvedPilgrim` est lazily-chargée une seule fois par instance
 * (chaque Policy/Controller a sa propre instance par requête dans Laravel).
 * Cela annule le N+1 déclenché par plusieurs gates sur la même requête.
 *
 * Usage :
 *   use ResolvesCurrentPilgrim;
 *   $pilgrim = $this->resolvePilgrim($user);
 */
trait ResolvesCurrentPilgrim
{
    /**
     * Cache par instance — null = non résolu, false = résolu mais aucun pilgrim.
     */
    private Pilgrim|false|null $resolvedPilgrim = null;

    /**
     * Retourne le Pilgrim associé à l'User, ou null si inexistant.
     * Premier appel : query SQL. Appels suivants : cache mémoire.
     */
    protected function resolvePilgrim(User $user): ?Pilgrim
    {
        if ($this->resolvedPilgrim === null) {
            $found = Pilgrim::query()->where('user_id', $user->id)->first();
            $this->resolvedPilgrim = $found instanceof Pilgrim ? $found : false;
        }

        return $this->resolvedPilgrim instanceof Pilgrim ? $this->resolvedPilgrim : null;
    }
}
