Example usage:

```require\_once('pdoutil.php');

$query = "SELECT user FROM users WHERE name IN :employee[]" AND job = :occupation";

$p = new PDOUtil($query);

$p-\>add\_data('employee[]', array('Alice', 'Bob'));
$p-\>add\_data('occupation', 'developer');

$p-\>finalize();

$stmt = $pdo-\>prepare($p-\>get\_query());
$stmt-\>execute($p-\>get\_params());
$user = $stmt-\>fetch();
```




