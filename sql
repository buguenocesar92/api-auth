SELECT
    roles.id AS role_id,
    roles.name AS role_name,
    permissions.id AS permission_id,
    permissions.name AS permission_name
FROM
    users
JOIN
    model_has_roles ON users.id = model_has_roles.model_id
JOIN
    roles ON model_has_roles.role_id = roles.id
LEFT JOIN
    role_has_permissions ON roles.id = role_has_permissions.role_id
LEFT JOIN
    permissions ON role_has_permissions.permission_id = permissions.id
WHERE
    users.tenant_id = 1
    AND model_has_roles.model_type = 'App\Models\User'
ORDER BY
    roles.name, permissions.name;
