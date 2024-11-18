function createStore(props) {
    const storeConfig = props;
    storeConfig.config = storeConfig.config ?? { globalStore: true };
    const $this = storeConfig.config.globalStore ? window : {};
    const variableKeys = Object.keys(storeConfig.variables);
    const reducerKeys = Object.keys(storeConfig.reducers);
    reducerKeys.forEach((key) => {
        if (!variableKeys.includes(key)) {
            throw new Error(`Undefined reducer: ${key}`);
        }
    });
    const createStoreObject = ($this) => {
        const obj = {};
        variableKeys.forEach((key) => {
            obj[key] = {
                value: storeConfig.variables[key],
                subs: [],
                onUpdate(callback) {
                    callback(this.value);
                    this.subs.push(callback);
                },
                update() {
                    this.subs.forEach((callback) => {
                        callback(this.value);
                    });
                },
            };
            Object.keys(storeConfig.reducers[key]).forEach((funName) => {
                obj[key][funName] = (args = obj[key].value) => {
                    const newValue = storeConfig.reducers[key][funName](args, obj[key].value);
                    if (newValue !== undefined && newValue !== obj[key].value) {
                        obj[key].value = newValue;
                        obj[key].update();
                    }
                };
            });
        });
        return obj;
    };
    Object.defineProperty($this, "Store", {
        value: createStoreObject($this),
        writable: false,
        enumerable: true,
        configurable: false,
    });
    return $this.Store;
}

createStore({
	variables: {
		counter: 0,
		dbRecords: null
	},
	reducers: {
		counter: {
			add: (v) => v + 1,
			min: (v) => v - 1
		},
		dbRecords: {
			async new(i) {
				const response = await fetch('/db/update');
				const json = await response.text();
				return json;
			}
		}
	},
});

