## Dashboard
### Deployment notes
```
# Before migration, please run:
php artisan vendor:publish --tag=config

php artisan migrate # [refresh ..]
php artisan db:seed # Widget type seeder

# testing
phpunit /packages/Rkesa/Dashboard
```

### Widget data & charts
<table>
    <thead>
        <tr>
            <th>Data id</th>
            <th>Data name</th>
            <th>According to interval</th>
            <th>Bar (1)</th>
            <th>Line (2)</th>
            <th>Pie (3)</th>
            <th>Table (4)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>1</td>
            <td>Service states</td>
            <td>Yes</td>
            <td>+</td>
            <td>-</td>
            <td>+</td>
            <td>-</td>
        </tr>
        <tr>
            <td>2</td>
            <td>Contact referrers</td>
            <td>Yes</td>
            <td>+</td>
            <td>-</td>
            <td>+</td>
            <td>-</td>
        </tr>
        <tr>
            <td>3</td>
            <td>Average time between creating a service and setting the status</td>
            <td>No</td>
            <td>+</td>
            <td>+</td>
            <td>-</td>
            <td>-</td>
        </tr>
        <tr>
            <td>4</td>
            <td>Average price of services from the status</td>
            <td>No</td>
            <td>+</td>
            <td>+</td>
            <td>-</td>
            <td>-</td>
        </tr>
        <tr>
            <td>5</td>
            <td>Number of services transferred to status</td>
            <td>No</td>
            <td>+</td>
            <td>+</td>
            <td>-</td>
            <td>-</td>
        </tr>
        <tr>
            <td>6</td>
            <td>The total cost of services translated into status</td>
            <td>No</td>
            <td>+</td>
            <td>+</td>
            <td>-</td>
            <td>-</td>
        </tr>
        <tr>
            <td>7</td>
            <td>Status duration</td>
            <td>Yes</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>+</td>
        </tr>
    </tbody>
</table>