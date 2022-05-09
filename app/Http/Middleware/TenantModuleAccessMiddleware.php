<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Arr;
use Route;

class TenantModuleAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $module)
    {
        $user = tenant(auth()->user()->id);

        if($user->hasRole('super admin')) {
            return $next($request);
        }

        $defaultBranch = Arr::first($user->branches, function ($value) {
            return $value->pivot->is_default;
        });
        $defaultWarehouse = Arr::first($user->warehouses, function ($value) {
            return $value->pivot->is_default;
        });
        $action = $this->_matchModuleAction(Route::current()->getActionMethod());
        
        if (
            $defaultBranch 
            && $defaultWarehouse
            && $defaultWarehouse->id === $defaultBranch->id
            && $user->hasPermissionTo($action.' '.$module)
        ) {
            if(
                in_array($action, ['create', 'update', 'delete']) 
                && $defaultWarehouse->id !== $request->warehouse_id
            ) {
                return response(['code' => 403, 'message' => 'Unauthorized Access'], 403);
            }

            return $next($request);
        }

        return response(['code' => 403, 'message' => 'Unauthorized Access'], 403);
    }

    protected function _matchModuleAction($action) {
        $match = null;

        switch ($action) {
            case 'index':
                $match = 'read';
                break;
            case 'show':
                $match = 'read';
                break;
            case 'store':
                $match = 'create';
                break;
            case 'destroy':
                $match = 'delete';
                break;
            case 'approve':
                $match = 'approve';
                break;
            case 'reject':
                $match = 'approve';
                break;
            
            default:
                $match = $action;
                break;
        }

        return $match;
    }
}
