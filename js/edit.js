
window.addEventListener("load", function()
{
    document.getElementById("nojs").style.display = "none";

    var oForm = document.getElementById("nm");
    oForm.style.display = "block";

    if (document.getElementById("un"))
    {
        document.getElementById("un").focus();
    }

    oForm.addEventListener("submit", function(e)
    {
        if (document.getElementById("un").value === "" || document.getElementById("pw").value === "")
        {
            document.getElementById("jserrors").innerHTML = "Both fields are required!";
            e.preventDefault();
        }
        else
        {
            var oPw = document.getElementById("pw");
            oPw.value = SHA256(SHA256(oPw.value) + document.getElementById("pk").value);
        }

    }, false);

}, false);


function SHA256(s){/* Angel Marin, Paul Johnston */ var chrsz=8,hexcase=0;function sa(x,y){var lsw=(x&0xFFFF)+(y&0xFFFF),msw=(x>>16)+(y>>16)+(lsw>>16);return(msw<<16)|(lsw&0xFFFF);}function S(X,n){return(X>>>n)|(X<<(32-n));}function R(X,n){return(X>>>n);}function Ch(x,y,z){return((x&y)^((~x)&z));}function Maj(x,y,z){return((x&y)^(x&z)^(y&z));}function S0256(x){return(S(x,2)^S(x,13)^S(x,22));}function S1256(x){return(S(x,6)^S(x,11)^S(x,25));}function G0256(x){return(S(x,7)^S(x,18)^R(x,3));}function G1256(x){return(S(x,17)^S(x,19)^R(x,10));}function cs256(m,l){var a,b,c,d,e,f,g,h,i,j,ml,T1,T2,K=[0x428A2F98,0x71374491,0xB5C0FBCF,0xE9B5DBA5,0x3956C25B,0x59F111F1,0x923F82A4,0xAB1C5ED5,0xD807AA98,0x12835B01,0x243185BE,0x550C7DC3,0x72BE5D74,0x80DEB1FE,0x9BDC06A7,0xC19BF174,0xE49B69C1,0xEFBE4786,0xFC19DC6,0x240CA1CC,0x2DE92C6F,0x4A7484AA,0x5CB0A9DC,0x76F988DA,0x983E5152,0xA831C66D,0xB00327C8,0xBF597FC7,0xC6E00BF3,0xD5A79147,0x6CA6351,0x14292967,0x27B70A85,0x2E1B2138,0x4D2C6DFC,0x53380D13,0x650A7354,0x766A0ABB,0x81C2C92E,0x92722C85,0xA2BFE8A1,0xA81A664B,0xC24B8B70,0xC76C51A3,0xD192E819,0xD6990624,0xF40E3585,0x106AA070,0x19A4C116,0x1E376C08,0x2748774C,0x34B0BCB5,0x391C0CB3,0x4ED8AA4A,0x5B9CCA4F,0x682E6FF3,0x748F82EE,0x78A5636F,0x84C87814,0x8CC70208,0x90BEFFFA,0xA4506CEB,0xBEF9A3F7,0xC67178F2],HASH=[0x6A09E667,0xBB67AE85,0x3C6EF372,0xA54FF53A,0x510E527F,0x9B05688C,0x1F83D9AB,0x5BE0CD19],W=new Array(64);m[l>>5]|=0x80<<(24-l%32);m[((l+64>>9)<<4)+15]=l;for(i=0,ml=m.length;i<ml;i+=16){a=HASH[0];b=HASH[1];c=HASH[2];d=HASH[3];e=HASH[4];f=HASH[5];g=HASH[6];h=HASH[7];for(j=-1;++j<64;){if(j<16){W[j]=m[j+i];}else{W[j]=sa(sa(sa(G1256(W[j-2]),W[j-7]),G0256(W[j-15])),W[j-16]);}T1=sa(sa(sa(sa(h,S1256(e)),Ch(e,f,g)),K[j]),W[j]);T2=sa(S0256(a),Maj(a,b,c));h=g;g=f;f=e;e=sa(d,T1);d=c;c=b;b=a;a=sa(T1,T2);}HASH[0]=sa(a,HASH[0]);HASH[1]=sa(b,HASH[1]);HASH[2]=sa(c,HASH[2]);HASH[3]=sa(d,HASH[3]);HASH[4]=sa(e,HASH[4]);HASH[5]=sa(f,HASH[5]);HASH[6]=sa(g,HASH[6]);HASH[7]=sa(h,HASH[7]);}return HASH;}

function str2binb(str){var bin=[],i=0,l=0,mask=(1<<chrsz)-1;for(l=str.length;i<l*chrsz;i+=chrsz){bin[i>>5]|=(str.charCodeAt(i/chrsz)&mask)<<(24-i%32);}return bin;}function Utf8Encode(string){string=string.replace(/\r\n/g,"\n");var utftext="",c="",n=-1,l=string.length;for(;++n<l;){c=string.charCodeAt(n);if(c<128){utftext+=String.fromCharCode(c);}else if((c>127)&&(c<2048)){utftext+=String.fromCharCode((c>>6)|192);utftext+=String.fromCharCode((c&63)|128);}else{utftext+=String.fromCharCode((c>>12)|224);utftext+=String.fromCharCode(((c>>6)&63)|128);utftext+=String.fromCharCode((c&63)|128);}}return utftext;}function binb2hex(binarray){var hex_tab=hexcase?"0123456789ABCDEF":"0123456789abcdef";var str="",i=-1,l=0;for(l=binarray.length*4;++i<l;){str+=hex_tab.charAt((binarray[i>>2]>>((3-i%4)*8+4))&0xF)+hex_tab.charAt((binarray[i>>2]>>((3-i%4)*8))&0xF);}return str;}s=Utf8Encode(s);return binb2hex(cs256(str2binb(s),s.length*chrsz));}
