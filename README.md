Example usage:

```require\_once('pdoutil.php');

$query = "SELECT user FROM users WHERE name IN :employee[]" AND job = :occupation";

$p = new PDOUtil($query);

$p->add_data('employee[]', array('Alice', 'Bob'));
$p->add_data('occupation', 'developer');

$p->finalize();

$stmt = $pdo->prepare($p->get_query());
$stmt->execute($p->get_params());
$user = $stmt->fetch();
```




