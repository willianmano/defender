<?php  namespace Artesaos\Defender\Middlewares;

use Closure;

/**
 * Class DefenderHasPermissionMiddleware
 * @package Artesaos\Defender
 */
class NeedsPermissionMiddleware extends AbstractDefenderMiddleware {

	/**
	 * @param \Illuminate\Http\Request $request
	 * @param callable $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$permissions   = $this->getPermissions($request);
		$anyPermission = $this->getAny($request);

		if (is_array($permissions) and count($permissions) > 0)
		{
			$canResult = true;

			foreach($permissions as $permission)
			{
				$canPermission = $this->user->can($permission);

				// Check if any permission is enough
				if ($anyPermission and $canPermission)
				{
					return $next($request);
				}

				$canResult = $canResult & $canPermission;
			}

			if ( ! $canResult )
			{
				return $this->forbiddenResponse();
			}
		}

		return $next($request);
	}

	/**
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 */
	private function getPermissions($request)
	{
		$routeActions = $this->getActions($request);

		$permissions = array_get($routeActions, 'can', []);

		return is_array($permissions) ? $permissions : (array) $permissions;
	}

}