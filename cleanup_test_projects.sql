-- Delete test projects
DELETE FROM project WHERE code LIKE 'ПКПО-%' OR code LIKE 'TEST-%';

-- Show remaining projects
SELECT code, name FROM project WHERE is_active = 1;
