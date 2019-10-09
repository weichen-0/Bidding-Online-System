<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>BIOS JSON Authentication Form</h1>
        <form method='POST' action='update-bid.php'>
            <table>
                <tr>
                    <td>User ID</td>
                    <td>
                        <input name='userid' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td>Amount</td>
                    <td>
                        <input name='amount' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td>Course</td>
                    <td>
                        <input name='course' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td>Section</td>
                    <td>
                        <input name='section' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td>Token</td>
                    <td>
                        <input name='token' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input name='Login' type='submit'/>
                    </td>
                </tr>
            </table>             
        </form>

    </body>
</html>