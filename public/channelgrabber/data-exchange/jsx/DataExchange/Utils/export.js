import ajax from "cg-common/src/js-vanilla/Common/Utils/xhr/ajax";
import fileDownload from "cg-common/src/js-vanilla/Common/Utils/xhr/fileDownload";

export const exportViaEmail = function(data, exportUrl, entity) {
    n.notice('We are processing your request...');
    ajax.request({
        method: 'POST',
        url: exportUrl,
        data,
        onSuccess: () => {
            n.success(`Please check your email for your ${entity} export.`)
        },
        onError: () => {
            n.error(`There was a problem exporting your ${entity}. Please contact support for assistance.`)
        }
    });
};

export const exportToBrowser = async function(data, exportUrl, entity) {
    n.notice(`We are exporting your ${entity}...`);
    const date = new Date();
    let fileDownloadResponse = await fileDownload.downloadBlob({
        url: exportUrl,
        data,
        desiredFilename: `${entity}-${date.toISOString().slice(0, 10)}_${date.getTime()}.csv';`
    });

    if (fileDownloadResponse.status !== 200) {
        n.error(`There was a problem exporting your ${entity}. Please contact support for assistance.`);
        return;
    }

    n.success(`Successfully exported your ${entity}.`);
};