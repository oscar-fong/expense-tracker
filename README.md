# Expense Tracker
Expense tracker CLI made with PHP.

Simple expense tracker to manage your finances. You can add, update, delete and view expenses, and it can also produce a summary of your expenses (even by month).

Examples:

```bash
./expense-tracker.php add --description "Rice" --amount 10
# Expense added successfully (ID: 1)
./expense-tracker.php add -d "Chicken" -a 30
# Expense added successfully (ID: 2)

./expense-tracker.php update 1 --description "Meat" --amount 25

./expense-tracker.php delete 1

./expense-tracker.php list
# ID  Date       Description Amount
# 2   2025-11-05 Chicken     $30

./expense-tracker.php summary
# Total expenses: $30

./expense-tracker.php summary --month 11
# Total expenses of November: $30
./expense-tracker.php summary -m 12
# Total expenses of December: $0
```

With this project I learned:
- Control flow statements (nested ifs and switchs)
- Organization of constant strings and values in enums (Error messages)
- Use of global variables and `global` keyword to avoid repeated and constant arguments in function calls
- Use of functions for repeated and common blocks of code
- Text formatting using printf
- Functional programming functions to filter and process array values (map, sum, filter, etc.)
