
import IEC7064 from "./IEC7064";

$(function()
{
    document.getElementById('btnGenerate').addEventListener('click', e =>
    {
        e.preventDefault();
        document.getElementById('isValid').innerHTML = 'unkown';

        const _IEC7064 = new IEC7064();
        const input = document.getElementById('inputString').value;
        const checksum = _IEC7064.checksum(input);
        document.getElementById('outputChecksum').value = checksum;
        document.getElementById('outputWholeString').value = input+''+checksum;
    });

    document.getElementById('btnValidate').addEventListener('click', e =>
    {
        e.preventDefault();
        document.getElementById('outputChecksum').value = null;
        document.getElementById('outputWholeString').value = null;

        const _IEC7064 = new IEC7064();
        const input = document.getElementById('inputString').value;
        const isValid = _IEC7064.verify(input);
        if(!isValid)
            document.getElementById('outputChecksum').value = _IEC7064.checksum(input);

        document.getElementById('isValid').innerHTML = isValid ? 'valid' : 'invalid';
    });
})
