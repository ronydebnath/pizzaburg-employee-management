<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BranchScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated, set the current branch context
        if (Auth::check() && Auth::user()->branch_id) {
            $branch = Branch::find(Auth::user()->branch_id);
            if ($branch) {
                // Store branch in request for easy access
                $request->merge(['current_branch' => $branch]);
                
                // Add branch to view data
                view()->share('currentBranch', $branch);
            }
        }

        return $next($request);
    }
}
