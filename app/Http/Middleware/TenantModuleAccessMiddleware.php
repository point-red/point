<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Exceptions\BranchNullException;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\WarehouseNullException;

use App\User;
use App\Model\Token;
use App\Model\Form;

class TenantModuleAccessMiddleware
{
    protected $form;
    protected $action;
    protected $user;
    protected $userDefaultBranch;
    protected $userDefaultWarehouse;
    protected $whitelistCheckPermissionOnDB = ['close'];
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $module)
    {
        $this->module = $module;
        $this->request = $request;

        $this->action = $this->_matchModuleAction($request->route()->getActionMethod());

        $this->_loadTenantUser();

        $this->_loadFormReference();

        try {
            if (
                $this->_isCheckPermissionOnDB()
                && !$this->user->hasRole('super admin')
                && !$this->user->hasPermissionTo($this->action.' '.$module) 
            )
            {
                throw new UnauthorizedException();
            }

            if ($this->action === 'read') return $next($request);

            $this->_hasDefaultBranch();

            $this->_hasDefaultWarehouse();

            $this->_isWarehouseBranchAsDefault();

            if(in_array($this->action, ['create', 'update'])) {
               $this->_isRequestWarehouseAsDefault($request);
            }

            if ($this->action === 'close' && (!$this->user->hasRole('super admin') && !$this->_hasCRUDPermissions())) {
                throw new UnauthorizedException();
            }

            return $next($request);
        } catch (\Throwable $th) {
            $code = $th->getCode();
            $message = $th->getMessage();

            $httpCode = $code === 0 ? 500 : $code;

            return response (['code' => $code, 'message' => $message], $httpCode);
        }
    }

    protected function _loadTenantUser()
    {
        if ($this->request->token && $this->request->approver_id) {
            $token = Token::where('user_id', $this->request->approver_id)
                ->where('token', $this->request->token)
                ->first();
            if (! $token) throw new UnauthorizedException();

            $this->user = $token->user;

            $authUser = User::find($this->user->id);
            auth()->guard('api')->setUser($authUser);
        } else {
            $this->user = tenant(auth()->user()->id);
        }

        $this->userDefaultBranch = Arr::first($this->user->branches, function ($value) {
            return $value->pivot->is_default;
        });
        $this->userDefaultWarehouse = Arr::first($this->user->warehouses, function ($value) {
            return $value->pivot->is_default;
        });
    }
    protected function _loadFormReference()
    {
        $requestParams = $this->request->route()->parameters;
        if (count($requestParams) > 0) {
            $formableId = array_values($requestParams)[0];
            $this->form = Form::where('formable_id', $formableId)
                ->where('formable_type', Str::studly($this->module))
                ->first();
        }else if ($this->request->ids) { //is action send approval by email
            $this->form = Form::whereIn('formable_id', $this->request->ids)
                ->where('formable_type', Str::studly($this->module))
                ->get();
        }
    }

    protected function _hasCRUDPermissions()
    {
        return $this->user->hasAnyPermission([
            'create '.$this->module,
            'read '.$this->module,
            'update '.$this->module,
            'delete '.$this->module
        ]);
    }

    protected function _hasDefaultBranch()
    {
        if (! $this->userDefaultBranch) throw new BranchNullException($this->action);

        if($this->form instanceof \Illuminate\Database\Eloquent\Collection) {
            foreach ($this->form as $form) {
                if ($form && $form->branch_id !== $this->userDefaultBranch->id) {
                    throw new BranchNullException($this->action);
                }
            }
            return true;
        }
        
        if ($this->form && $this->form->branch_id !== $this->userDefaultBranch->id) {
            throw new BranchNullException($this->action);
        }

        return true;
    }
    protected function _hasDefaultWarehouse()
    {
        if (! $this->userDefaultWarehouse) throw new WarehouseNullException($this->action);

        if($this->form instanceof \Illuminate\Database\Eloquent\Collection) {
            foreach ($this->form as $form) {
                if ($form && $form->formable->warehouse_id !== $this->userDefaultWarehouse->id ) {
                    throw new WarehouseNullException($this->action);
                }
            }
            return true;
        }

        if ($this->form && $this->form->formable->warehouse_id !== $this->userDefaultWarehouse->id) {
            throw new WarehouseNullException($this->action);
        }

        return true;
    }

    protected function _isCheckPermissionOnDB ()
    {
        return !in_array($this->action, $this->whitelistCheckPermissionOnDB);
    }

    protected function _isWarehouseBranchAsDefault()
    {
        $defaultBranch = $this->userDefaultBranch;
        $defaultWarehouse = $this->userDefaultWarehouse;

        if ($defaultWarehouse->branch_id !== $defaultBranch->id) {
            throw new Exception("Branch " . optional($defaultWarehouse->branch)->name . " not set as default", 422);
        }
    }

    protected function _isRequestWarehouseAsDefault()
    {
        $request = $this->request;
        $defaultWarehouse = $this->userDefaultWarehouse;

        //$request->ids to check is action send approval by email
        if(!$request->ids && ($defaultWarehouse->id !== $request->warehouse_id)) {
            throw new Exception('Warehouse ' . $request->warehouse_name . ' not set as default', 422);     
        }
    }

    protected function _matchModuleAction($action) {
        if (in_array($action, ['index', 'show', 'showReceipt', 'export'])) {
            return 'read';
        }

        if (in_array($action, ['store', 'sendApproval'])) return 'create';

        if (in_array($action, ['destroy'])) return 'delete';

        if (in_array($action, ['approve', 'reject'])) return 'approve';

        return $action;
    }
}
