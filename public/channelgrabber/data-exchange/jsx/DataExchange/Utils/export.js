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
        desiredFilename: `${entity}-${date.toISOString().slice(0, 10)}_${date.getTime()}.csv`,
        validateResponseCallback: async (response) => {
            if (!response) {
                return false;
            }

            try {
                const responseText = await response.text();
                const responseJson = JSON.parse(responseText);

                if (responseJson.error && responseJson.error.length > 0) {
                    n.error(responseJson.error);
                    return  false;
                }

                return true;
            } catch (error) {
                // the response could not be parsed as JSON, assuming it's has the valid file contents instead
                return true;
            }
        }
    });

    if (fileDownloadResponse.status !== 200) {
        n.error(`There was a problem exporting your ${entity}. Please contact support for assistance.`);
        return;
    }

    n.success(`Successfully exported your ${entity}.`);
};