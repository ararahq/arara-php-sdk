# Changelog

Todas as mudanças relevantes do `ararahq/sdk` (PHP). Formato baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.1.0/), versionamento [SemVer](https://semver.org/lang/pt-BR/).

## [2.0.0] - 2026-09-24

### Breaking
- Removidos `$sdk->users`, `$sdk->organizations` e `$sdk->apiKeys`: nenhuma dessas rotas responde a API key (sempre 403). Para o usuário atual use `$sdk->auth()->me()` (`GET /auth/me`, exige chave ADMIN).
- `templates->get()`, `templates->delete()` recebem o `id` (UUID) do template, não o nome. Para achar pelo nome: `templates->list(name: 'welcome')`.
- `templates->list()` e `smartLinks->list()` devolvem `Arara\Pagination\PaginatedResponse` (`data` + `pagination`) em vez de array cru, e aceitam `page`/`size`.
- `messages->send()` aceita `whatsapp:+55...`, `+55...` ou só dígitos (quem valida é a API). A mídia vai como `mediaUrl`.
- Todo 403 vira `ForbiddenException` (antes era `AraraException` genérica): sem envelope ou vazio (chave sem permissão, recurso de outra organização) com `errorCode` nulo e a mensagem do corpo quando houver; com envelope, `errorCode` preenchido; `PLAN_FEATURE_LOCKED` vira a subclasse `PlanFeatureLockedException`. 401 continua `AuthenticationException`. 5xx (502, 503...) vira `InternalServerException` com o status real.
- Retry automático nunca repete POST/PATCH sem `Idempotency-Key`.

### Adicionado
- `Idempotency-Key` automático (UUID v4) em `messages->send()`, `messages->sendBatch()` e `campaigns->create()`, reaproveitado em todos os retries da mesma chamada. O caller pode passar a própria chave.
- `PlanFeatureLockedException` (403 `PLAN_FEATURE_LOCKED`) com `feature`, `currentPlan`, `upgradeTo`; `ForbiddenException` para os demais 403.
- `AraraException` expõe `details` e `retryAfter`.
- `messages->get($id)`, `messages->sendBatch($templateName, $messages)` (até 1000).
- `templates->getStatus($id)`, `templates->analytics($id = null, $period = '30d')`.
- `$sdk->optOuts` (`list`, `add`, `get`, `remove`).
- Workflow de release: a cada push na `main`, se `Arara::VERSION` ainda não tem tag, cria tag e GitHub release (Packagist atualiza pelo webhook).

## [1.8.1]
- Versão anterior publicada no Packagist.
