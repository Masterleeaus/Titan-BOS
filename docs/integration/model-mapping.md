# WorkCore — Model Mapping

## Namespace strategy

WorkCore models are placed under `App\Models\WorkSuite\<Domain>` to avoid collision
with host models. The host's model files are never modified.

## CRM domain (Phase 1 — namespace-copied)

| WorkSuite class | New namespace location | Table | Notes |
|-----------------|------------------------|-------|-------|
| `Lead` | `App\Models\WorkSuite\Crm\Lead` | `leads` | WorkSuite lead (different from `crm_leads`) |
| `LeadAgent` | `App\Models\WorkSuite\Crm\LeadAgent` | `lead_agents` | |
| `LeadCategory` | `App\Models\WorkSuite\Crm\LeadCategory` | `lead_categories` | |
| `LeadCustomForm` | `App\Models\WorkSuite\Crm\LeadCustomForm` | `lead_custom_forms` | |
| `LeadNote` | `App\Models\WorkSuite\Crm\LeadNote` | `lead_notes` | |
| `LeadPipeline` | `App\Models\WorkSuite\Crm\LeadPipeline` | `lead_pipelines` | |
| `LeadPipelineStages` | `App\Models\WorkSuite\Crm\LeadPipelineStages` | `lead_pipeline_stages` | |
| `LeadProduct` | `App\Models\WorkSuite\Crm\LeadProduct` | `lead_products` | |
| `LeadSetting` | `App\Models\WorkSuite\Crm\LeadSetting` | `lead_settings` | |
| `LeadSource` | `App\Models\WorkSuite\Crm\LeadSource` | `lead_sources` | |
| `LeadStatus` | `App\Models\WorkSuite\Crm\LeadStatus` | `lead_statuses` | |
| `LeadUserNote` | `App\Models\WorkSuite\Crm\LeadUserNote` | `lead_user_notes` | |
| `Deal` | `App\Models\WorkSuite\Crm\Deal` | `deals` | WorkSuite deal (different from `crm_deals`) |
| `DealFile` | `App\Models\WorkSuite\Crm\DealFile` | `deal_files` | |
| `DealFollowUp` | `App\Models\WorkSuite\Crm\DealFollowUp` | `deal_follow_ups` | |
| `DealHistory` | `App\Models\WorkSuite\Crm\DealHistory` | `deal_histories` | |
| `DealNote` | `App\Models\WorkSuite\Crm\DealNote` | `deal_notes` | |
| `ClientDetails` | `App\Models\WorkSuite\Crm\ClientDetails` | `client_details` | Maps to `customers` surface |
| `ClientCategory` | `App\Models\WorkSuite\Crm\ClientCategory` | `client_categories` | |
| `ClientContact` | `App\Models\WorkSuite\Crm\ClientContact` | `client_contacts` | |
| `ClientDocument` | `App\Models\WorkSuite\Crm\ClientDocument` | `client_documents` | |
| `ClientNote` | `App\Models\WorkSuite\Crm\ClientNote` | `client_notes` | |
| `Contract` | `App\Models\WorkSuite\Crm\Contract` | `contracts` | Maps to `service agreements` |
| `EstimateRequest` | `App\Models\WorkSuite\Crm\EstimateRequest` | `estimate_requests` | |

## Host model protection

The following host models are **never modified** by WorkCore:

| Host model | Location | Protected from |
|------------|----------|----------------|
| `User` | `App\Models\User` | No columns added, no relationships injected |
| `Company` | `App\Models\Company` | Not used as tenant boundary |
| `Extension` | `App\Models\Extension` | Not touched |
| `Team` | `App\Models\Team` | Not touched (AI collaboration team) |

## Tenancy alignment

WorkSuite models use `company_id` as their tenant column. In Titan-BOS, the primary
isolation is `user_id`. The WorkSuiteServiceProvider reads:

```php
config('worksuite.tenancy.tenant_column') // defaults to 'company_id'
```

**Resolution:** When wiring WorkSuite models to actual queries, add a global scope or
query macro that maps `company_id` from `auth()->user()->id` (or a user-owned company
record). This is deferred to Phase 2 model wiring.

WorkSuite also references `team_id`. The config key `worksuite.tenancy.legacy_team_column`
(`team_id`) preserves compatibility. Mirror `team_id = user->team_id` where needed.

## Pending model work (Phase 2)

1. Rewrite intra-domain `use App\Models\` statements to `use App\Models\WorkSuite\Crm\`
2. Extract and namespace-rewrite remaining domain slices (Finance, HR, Projects, Support, Platform)
3. Implement global scope or model boot method for tenancy alignment
4. Wire stub controllers to actual models
