<script setup>
import * as Card from '@/Bits/Components/Card/Card.js';
import translate from "@/utils/translator/Translator";
import {ref, watch} from "vue";

const props = defineProps({
  product: Object,
  productEditModel: Object
});

const disclaimer = ref('');

const syncDisclaimer = (value) => {
  disclaimer.value = value ?? '';
};

watch(() => props.product?.detail?.other_info?.disclaimer, (value) => {
  syncDisclaimer(value);
}, {immediate: true});

const updateDisclaimer = (value) => {
  syncDisclaimer(value);
  props.productEditModel.onChangeInputField('disclaimer', disclaimer.value);
};
</script>

<template>
  <div class="fct-product-disclaimer-wrap">
    <Card.Container>
      <Card.Header
          :title="translate('Disclaimer')"
          :desc="translate('Add an optional disclaimer that will show on the product page.')"
          border_bottom
      />
      <Card.Body>
        <el-form label-position="top" require-asterisk-position="right">
          <el-form-item :label="translate('Disclaimer text')">
            <el-input
                type="textarea"
                :rows="4"
                :placeholder="translate('Add any important notes or warnings for this product')"
                :model-value="disclaimer"
                @input="updateDisclaimer"
            />
          </el-form-item>
        </el-form>
      </Card.Body>
    </Card.Container>
  </div>
</template>
