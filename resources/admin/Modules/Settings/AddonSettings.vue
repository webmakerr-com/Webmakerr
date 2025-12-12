<template>
  <div class="setting-wrap wm-container-enhanced">
    <div class="bg-white rounded p-6 dark:bg-dark-700" v-if="formLoading">
      <el-skeleton :loading="formLoading" animated>
        <template #template>
          <div class="grid gap-3 mb-6">
            <el-skeleton-item variant="p" class="w-[20%]"/>
            <el-skeleton-item variant="p"/>
          </div>
          <div class="grid gap-3 mb-6">
            <el-skeleton-item variant="p" class="w-[20%]"/>
            <el-skeleton-item variant="p"/>
          </div>
          <div class="grid gap-3 mb-6">
            <el-skeleton-item variant="p" class="w-[20%]"/>
            <el-skeleton-item variant="p"/>
          </div>
          <div class="grid gap-3">
            <el-skeleton-item variant="p" class="w-[20%]"/>
            <el-skeleton-item variant="p"/>
          </div>
        </template>
      </el-skeleton>
    </div>

    <template v-if="!formLoading">
      <el-form v-if="form.isReady">
        <VueForm
            v-if="hasSettings"
            :form="form"
            :showSubmitButton="true"
            @onSubmitButtonClick="saveSettings"
            :submitButtonText="translate('Save Settings')"
            :loading="saving"
            @on-change="(value) => {}"
            :validation-errors="validationErrors"
        />
        <Card.Container v-else>
          <Card.Header :title="translate('Features & addon')" border_bottom/>
          <Card.Body>
            <Empty class="fct-addon-empty-state" icon="Empty/Integrations" :text="translate('No module settings found.')" />
          </Card.Body>
        </Card.Container>
      </el-form>

      <Card.Container class="mt-6">
        <Card.Header :title="translate('Pro Add-ons (Preview)')" border_bottom/>
        <Card.Body>
          <div class="space-y-4">
            <div
                v-for="addon in proAddons"
                :key="addon.key"
                class="flex items-start justify-between gap-4 py-4 border border-gray-divider border-x-0 border-t-0 last:border-b-0 dark:border-dark-500"
            >
              <div>
                <p class="text-base font-semibold text-gray-900 dark:text-white mb-1">
                  {{ addon.title }}
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-1">
                  {{ addon.description }}
                </p>
                <span class="inline-flex items-center text-xs font-medium uppercase tracking-wide text-primary-500">
                  {{ translate('Pro Feature') }}
                </span>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-sm text-gray-700 dark:text-gray-200">{{ translate('Activate') }}</span>
                <el-switch
                    v-model="proAddonStates[addon.key]"
                    @change="(value) => handleProToggle(addon, value)"
                    :active-value="true"
                    :inactive-value="false"
                />
              </div>
            </div>
          </div>
        </Card.Body>
      </Card.Container>
    </template>

    <el-dialog
        v-model="showProModal"
        :title="translate('This is a Pro feature')"
        width="420px"
        class="fct-pro-addon-dialog"
    >
      <p class="text-sm text-gray-700 dark:text-gray-200 mb-4">
        {{ translate('This is a Pro feature') }}
      </p>
      <div class="flex justify-end gap-3">
        <el-button @click="showProModal = false">{{ translate('Close') }}</el-button>
        <a :href="upgradeUrl" target="_blank" rel="noopener" class="no-underline">
          <el-button type="primary">{{ translate('Upgrade to Pro') }}</el-button>
        </a>
      </div>
    </el-dialog>
  </div>
  <!-- .setting-wrap -->
</template>

<script setup>

import {
  getCurrentInstance,
  nextTick,
  onMounted,
  ref,
} from "vue";
import {useSaveShortcut} from '@/mixin/saveButtonShortcutMixin.js';
import VueForm from "@/Bits/Components/Form/VueForm.vue";
import {useSettingsModel} from "@/Models/SettingsModel";
import Empty from "@/Bits/Components/Table/Empty.vue";
import * as Card from "@/Bits/Components/Card/Card.js";
import translate from "@/utils/translator/Translator";

const settingsModel = useSettingsModel();
const {form} = settingsModel.data;

const saveShortcut = useSaveShortcut();


const selfRef = getCurrentInstance().ctx;


defineOptions({
  name: "StoreSettings",
});

const settings = ref([]);
const fields = ref([]);
const loading = ref(true);
const saving = ref(false);
const formLoading = ref(true);
const validationErrors = ref({});
const hasSettings = ref(false);
const showProModal = ref(false);
const upgradeUrl = 'https://webmakerr.com/item/webmakerr-pro-plugin';
const proAddons = ref([
  {
    key: 'license_module',
    title: translate('License Module'),
    description: translate('Manage license keys, activations, and entitlements directly inside Webmakerr.'),
  },
  {
    key: 'advanced_reporting',
    title: translate('Advanced Reporting'),
    description: translate('Unlock detailed revenue analytics, retention cohorts, and sales trend insights.'),
  },
  {
    key: 'priority_support',
    title: translate('Priority Support & SLA'),
    description: translate('Get priority ticket routing, faster response times, and SLA-backed assistance.'),
  }
]);
const proAddonStates = ref(proAddons.value.reduce((acc, addon) => {
  acc[addon.key] = false;
  return acc;
}, {}));

const saveSettings = () => {

  saving.value = true;
  // let value = JSON.parse(JSON.stringify(form.values));
  let value = form.values;
  selfRef
      .$post('settings/modules', {
        ...value,
      })
      .then((response) => {
        selfRef.handleSuccess(translate("Settings saved successfully"));
        setTimeout(() => {
          window.location.reload();
        }, 300)
      })
      .catch((errors) => {
        if (errors && errors.message) {
          return selfRef.handleError(errors);
        }
        validationErrors.value = errors;
      })
      .finally(() => {
        saving.value = false;
        formLoading.value = false;
        loading.value = false;
      });
};


const getSettings = () => {
  loading.value = true;
  selfRef.$get('settings/modules', {})
      .then((response) => {
        form.setSchema(response.fields).setDefaults(response.settings).initForm();
        settings.value = response.settings;
        fields.value = response.fields;
        formLoading.value = false;
        saving.value = false;

        const schema = response.fields?.modules_settings?.schema || [];
        let hasFields = false;
        if (Array.isArray(schema)) {
          hasFields = schema.length > 0;
        } else {
          hasFields = Object.keys(schema).length > 0;
        }

        hasSettings.value = hasFields;
      })
      .finally(() => {
        loading.value = false;
      });
};

const handleProToggle = (addon, isActivated) => {
  if (isActivated) {
    showProModal.value = true;
  }
  nextTick(() => {
    proAddonStates.value[addon.key] = false;
  });
};

onMounted(() => {
  getSettings();
});

saveShortcut.onSave(saveSettings);
</script>
