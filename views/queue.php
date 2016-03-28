<div id="idAllTubes">
    <section id="summaryTable">
        <div class="row">
            <div class="col-sm-12">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Data</th>
                            <th>State</th>
                            <th>Priority</th>
                            <th>Ready</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $j) { ?>
                            <tr>
                                <td><?= $j['id'] ?></td>
                                <td><?= $j['data'] ?></td>
                                <td><?= $j['state'] ?></td>
                                <td><?= $j['priority'] ?></td>
                                <td><?= $j['ready'] ?></td>
                                <td><?= $j['start'] ?></td>
                                <td><?= $j['end'] ?></td>
                                <td><a href="#" class="btn btn-xs btn-danger">Delete</a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>