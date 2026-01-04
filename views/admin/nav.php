<nav>
    <table cellpadding="4" cellspacing="4">
        <tr>
            <td>
                <form method="get">
                    <input type="hidden" name="view" value="entries">
                    <button type="submit">Entries</button>
                </form>
            </td>
            <td>
                <form method="get">
                    <input type="hidden" name="view" value="add">
                    <button type="submit">Add entry</button>
                </form>
            </td>
            <td>
                <form method="get">
                    <input type="hidden" name="view" value="translate">
                    <button type="submit">Translate</button>
                </form>
            </td>
            <td>
                <form method="get">
                    <input type="hidden" name="view" value="languages">
                    <button type="submit">Languages</button>
                </form>
            </td>
            <td>
                <form method="get">
                    <input type="hidden" name="view" value="ui">
                    <button type="submit">UI strings</button>
                </form>
            </td>
            <td>
                <form method="get">
                    <input type="hidden" name="view" value="palette">
                    <button type="submit">Palette</button>
                </form>
            </td>
            <td>
                <form method="post">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit">Logout</button>
                </form>
            </td>
            <td>
                <form action="index.php" method="get">
                    <button type="submit">View public search</button>
                </form>
            </td>
        </tr>
    </table>
</nav>
