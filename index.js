const file_system = require('fs'),
    chalk = require('chalk'),
    archiver = require('archiver'),
    output = file_system.createWriteStream('yandex_metrika_graph.zip'),
    archive = archiver('zip');

output.on('close', function () {
    console.log('Generate ' + chalk.cyan('yandex_metrika_graph.zip') + chalk.yellowBright(' => ' + archive.pointer() + ' bytes'));
});

archive.on('error', function(err){
    throw err;
});

archive.pipe(output);

archive.directory('assets/', 'yandex_metrika_graph/assets');
archive.directory('install/', 'yandex_metrika_graph/install');

archive.finalize();
