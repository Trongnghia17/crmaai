<?php
use Illuminate\Support\Facades\DB;

if (!function_exists('build_query_sort_field')) {
    /**
     * Generates the query by user id
     */
    function build_query_sort_field(&$query, $sortField): void
    {
        if ($sortField == '' || !$sortField) {
            $query->orderBy('updated_at');
        } else {
            $isDesc = str_starts_with($sortField, '-');
            $field = $isDesc ? substr($sortField, 1) : $sortField;

            if ($isDesc) {
                $query->orderBy($field);
            } else {
                $query->orderByDesc($field);
            }
        }
    }

    function build_query_by_user_id(&$query, $user)
    {
        $query->where('user_id', $user->id);
    }

    function build_query_by_user_id_with_table_name(&$query, $user, $tableName)
    {
        if ($user->type === 1) {
            return;
        }
        $query->where($tableName . '.user_id', $user->id);
    }
}
