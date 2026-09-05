<?php

namespace App\Http\Middleware;

use App\Services\Compliance\AuditLogger;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditSensitiveAction
{
    public function __construct(
        private readonly AuditLogger $auditLogger
    ) {}

    public function handle(
        Request $request,
        Closure $next,
        string $category = 'compliance',
        string $event = 'sensitive.access',
        string $action = 'read',
    ): Response {
        $subject =
            $this->resolveSubject(
                $request
            );

        try {
            /** @var Response $response */
            $response =
                $next($request);

            $this->auditLogger->record(
                category: $category,
                event: $event,
                action: $action,
                subject: $subject,
                result: $response->getStatusCode()
                    < 400
                        ? 'success'
                        : 'failure',
                metadata: [
                    'route' => $request->route()?->getName(),

                    'method' => $request->method(),

                    'status_code' => $response->getStatusCode(),
                ],
                request: $request,
            );

            return $response;
        } catch (Throwable $exception) {
            $this->auditLogger->record(
                category: $category,
                event: $event,
                action: $action,
                subject: $subject,
                result: 'failure',
                metadata: [
                    'route' => $request->route()?->getName(),

                    'method' => $request->method(),

                    'exception_class' => $exception::class,
                ],
                request: $request,
            );

            throw $exception;
        }
    }

    private function resolveSubject(
        Request $request
    ): ?Model {
        $parameters =
            $request->route()?->parameters()
            ?? [];

        foreach (
            $parameters as $parameter
        ) {
            if (
                $parameter instanceof Model
            ) {
                return $parameter;
            }
        }

        return null;
    }
}
