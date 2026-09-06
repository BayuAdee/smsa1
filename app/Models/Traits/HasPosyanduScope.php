<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait HasPosyanduScope
{
    protected static function bootHasPosyanduScope(): void
    {
        static::addGlobalScope('posyandu_scope', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                if ($user->isKader() && $user->posyandu_id) {
                    $builder->where(function (Builder $q) use ($user) {
                        $modelTable = $q->getModel()->getTable();
                        if ($modelTable === 'anaks') {
                            $q->where('anaks.posyandu_id', $user->posyandu_id);
                        } elseif ($modelTable === 'pengukurans') {
                            $q->whereHas('anak', function (Builder $aq) use ($user) {
                                $aq->where('anaks.posyandu_id', $user->posyandu_id);
                            });
                        } elseif ($modelTable === 'hasil_saw') {
                            $q->whereHas('anak', function (Builder $aq) use ($user) {
                                $aq->where('anaks.posyandu_id', $user->posyandu_id);
                            });
                        }
                    });
                } elseif ($user->isBidan()) {
                    $selectedPosyanduId = session('selected_posyandu_id');
                    if ($selectedPosyanduId && $selectedPosyanduId !== 'all') {
                        $builder->where(function (Builder $q) use ($selectedPosyanduId) {
                            $modelTable = $q->getModel()->getTable();
                            if ($modelTable === 'anaks') {
                                $q->where('anaks.posyandu_id', $selectedPosyanduId);
                            } elseif ($modelTable === 'pengukurans') {
                                $q->whereHas('anak', function (Builder $aq) use ($selectedPosyanduId) {
                                    $aq->where('anaks.posyandu_id', $selectedPosyanduId);
                                });
                            } elseif ($modelTable === 'hasil_saw') {
                                $q->whereHas('anak', function (Builder $aq) use ($selectedPosyanduId) {
                                    $aq->where('anaks.posyandu_id', $selectedPosyanduId);
                                });
                            }
                        });
                    }
                }
            }
        });
    }

    public function scopeWithoutPosyanduScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('posyandu_scope');
    }
}
