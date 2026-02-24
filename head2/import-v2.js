import {Datetime_global} from "Datetime_global";
import {ClockTime, RelativeTime} from "RelativeTimeChecker";

globalThis.Datetime_global = Datetime_global;
globalThis.RelativeTime = RelativeTime;
globalThis.ClockTime = ClockTime;

// time[data-toLocalTime]

globalThis.domContentLoadedPromise.then(function () {
    document.querySelectorAll('time[data-toLocalTime]').forEach(each => {
        const date = new Date(each.dateTime); // noinspection JSCheckFunctionSignatures
        if (isNaN(date)) return (each.innerText = "Invalid Date");
        switch (each.dataset.tolocaltime?.toLowerCase().replaceAll(/-/g, '')) {
            case "datetime":
                each.innerText = date.toString().slice(0, 24);
                break;
            case "timemilliseconds":
                each.innerText = date.getTime().toString();
                break;
            case "timeseconds":
                each.innerText = (date.setMilliseconds(0) / 1000).toString();
                break;
            case "fulldate":
            case "tostring":
                each.innerText = date.toString();
                break;
            case "dateonly":
                each.innerText = date.toDateString();
                break;
            case "timeonly":
                each.innerText = date.toTimeString().slice(0, 8);
                break;
            case "timezone":
                each.innerText = date.toTimeString().slice(9);
                break
            case "isostring":
            case "toisostring":
                each.innerText = date.toISOString();
                break;
            case "utcstring":
            case "toutcstring":
                each.innerText = date.toUTCString();
                break;
            case "localestring":
            case "tolocalestring":
                each.innerText = date.toLocaleString(each.dataset.locale ?? navigator.language);
                break;
            default:
                console.error(TypeError(`Unknown data-toLocalTime ("${each.dataset.tolocaltime}")`));
        }
    });
});
