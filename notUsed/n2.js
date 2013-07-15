function parseCity(a, b, c, d) {
    var $createHelper;
    var e = {};
    $I.JKVFIP.prototype.TKESGF.call(this, a, b);
    var f;
    this.isLocked = -1;
    this.isProtected = -1;
    this.supportLaterDStart = -1;
    this.supportlaterend = -1;
    this.move1 = -1;
    this.move2 = -1;
    this.end = -1;

    var g = $I.MWQOJC.EBDMNN(c, d);
    this.isAttacked = ((g & 1) != 0);
    var isLocked = (((g >> 1) & 1) != 0);
    var isProtected = (((g >> 2) & 1) != 0);
    var isAltered = (((g >> 3) & 1) != 0);
    var hasCoolDown = (((g >> 4) & 1) != 0);
    var hasRecovery = (((g >> 5) & 1) != 0);

    var m = (((g >> 6) & 1) != 0);
    var n = (((g >> 7) & 1) != 0);

    this.level = ((g >> 8) & 0xff);
    this.radius = ((g >> 0x10) & 15);
    this.playerId = ((g >> 0x16) & 0x3ff);
    d += 5;
    if (isLocked) {
        this.isLocked = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (isProtected) {
        this.isProtected = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (isAltered) {
        this.supportLaterDStart = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
        this.supportlaterend = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (hasCoolDown) {
        this.move1 = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (hasRecovery) {
        this.move2 = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
        this.UMVGOH = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
    }
    if (m) {
        this.end = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
    }
    this.conditionbuildings = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
    d += f;
    if (n) {
        this.conddefence = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
        d += f;
    } else {
        this.conddefence = -1;
    }
    this.defautoreps = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
    d += f;
    this.id = (e.$r = $I.MWQOJC.RFAFAJ(c, d, e), f = e.c, e.$r);
    this.name = c.substr((d + f));
    return this;
}

function parsePOI(a, b, c, d, e) {
    var $createHelper;
    var out = {};
    $I.JKVFIP.prototype.TKESGF.call(this, a, b);
    var size;
    var poiData = $I.MWQOJC.DCSVMJ(d, e);
    this.level = (poiData & 0xff);
    this.poiType = ((poiData >> 8) & $I.XREQKX.Defense);
    this.EOEUAY = ((poiData >> 11) & 0x3ff);
    e += 4;
    this.ZYCZMM = (out.$r = $I.MWQOJC.RFAFAJ(d, e, out), size = out.c, out.$r);
    return this;
}