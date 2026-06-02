<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gridstack@10/dist/gridstack.min.css"/>
<style>
  /* GridStack base overrides */
  .grid-stack { background: transparent; }
  .grid-stack-item-content { overflow: auto; }

  /* Edit mode visual cue */
  body.gs-edit-mode .grid-stack-item-content {
    outline: 2px dashed rgba(75, 73, 172, 0.4);
    outline-offset: -2px;
  }
  body.gs-edit-mode .grid-stack {
    background-image: linear-gradient(rgba(75,73,172,.04) 1px, transparent 1px),
                      linear-gradient(90deg, rgba(75,73,172,.04) 1px, transparent 1px);
    background-size: calc(100% / 12) 60px;
  }

  /* Floating pencil FAB */
  #gs-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
  }
  #gs-fab-main {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #4B49AC;
    color: #fff;
    border: none;
    box-shadow: 0 4px 14px rgba(75,73,172,.45);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    cursor: pointer;
    transition: background .2s, transform .15s;
  }
  #gs-fab-main:hover { background: #3b3a8c; transform: scale(1.06); }
  #gs-fab-main.active { background: #e74c3c; }

  /* Edit toolbar — fixed, centered at bottom */
  #gs-edit-toolbar {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 9998;
    display: none;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,.97);
    padding: 8px 16px;
    border-radius: 32px;
    box-shadow: 0 4px 20px rgba(0,0,0,.18);
    white-space: nowrap;
  }
  #gs-edit-toolbar.visible { display: flex; }

  .gs-tb-btn { padding: 6px 18px; border-radius: 20px; border: none; font-size: 13px; font-weight: 500; cursor: pointer; transition: opacity .15s; }
  .gs-tb-btn:hover { opacity: .82; }
  .gs-tb-btn-save   { background: #27ae60; color: #fff; }
  .gs-tb-btn-reset  { background: #f39c12; color: #fff; }
  .gs-tb-btn-cancel { background: #f0f0f0; color: #333; }

  /* Cards always fill their grid item */
  .gs-card { height: 100%; display: flex; flex-direction: column; }
  .gs-card .card-body { flex: 1; overflow: auto; }

  /* Hide card button */
  .gs-hide-btn {
    display: none;
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(231,76,60,0.1);
    border: 1px solid rgba(231,76,60,0.35);
    color: #e74c3c;
    font-size: 13px;
    cursor: pointer;
    align-items: center;
    justify-content: center;
    z-index: 100;
    transition: background .15s, color .15s, border-color .15s;
    line-height: 1;
  }
  .gs-hide-btn:hover { background: #e74c3c; color: #fff; }
  body.gs-edit-mode .gs-hide-btn { display: flex; }

  /* Hidden card state */
  .gs-card-hidden .grid-stack-item-content {
    opacity: 0.25;
    pointer-events: none;
    filter: grayscale(0.4);
  }
  .gs-card-hidden .gs-hide-btn {
    pointer-events: all;
    background: rgba(39,174,96,0.1);
    border-color: rgba(39,174,96,0.35);
    color: #27ae60;
  }
  .gs-card-hidden .gs-hide-btn:hover { background: #27ae60; color: #fff; }

  /* Hide edit controls on mobile */
  @media (max-width: 767px) {
    #gs-fab, #gs-edit-toolbar { display: none !important; }
  }
</style>
