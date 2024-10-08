<?php

namespace App\Http\Controllers;

use App\Http\Requests\IncomeCategoryFormRequest;
use App\Http\Requests\IncomeCategoryListRequest;
use App\Interfaces\IncomeCategoryServiceInterface;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class IncomeCategoryController extends Controller {
    public function __construct(
        protected IncomeCategoryServiceInterface $incomeCategoryService
    ) {}
    
    public function index(Request $request): ApiResponse {
        $id = $request->query('id');
        if(!empty($id)) {
            return $this->details($id);
        }
        else {
            $validatedData = $request->validate((new IncomeCategoryListRequest())->rules());
            $incomeCategoryListRequest = new IncomeCategoryListRequest($validatedData);
            return $this->list($incomeCategoryListRequest);
        }
    }

    //Think about a better way to return data with the paginator response.
    private function list(IncomeCategoryListRequest $request): ApiResponse {
        $response = new ApiResponse();
        $filterSet = $request->input();

        $result = $this->incomeCategoryService->list($filterSet);

        if(!empty($result)) {
            $response->data = [
                'count' => count($result),
                'items' => $result
            ];
            $response->message = 'Income category list loaded successfully.';
        }
        else {
            $response->status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response->message = 'An error occured while trying to load the income category list.';
        }

        return $response;
    }

    private function details($id): ApiResponse {
        $response = new ApiResponse();

        if(empty(intval($id))) {
            $response->status = Response::HTTP_BAD_REQUEST;
            $response->message = 'Invalid arguments. A numeric income category id must be provided.';
            return $response;
        }
        
        $incomeCategory = $this->incomeCategoryService->details($id);

        if(!empty($incomeCategory)) {
            $response->data = [
                'id' => $incomeCategory->id,
                'symbol' => $incomeCategory->symbol,
                'name' => $incomeCategory->translation->name,
                'description' => $incomeCategory->translation->description
            ];
            $response->message = 'Income category details loaded successfully.';
        }
        else {
            $response->status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response->message = 'An error occured while trying to load the income category details or there is no income category with this id.';
        }

        return $response;
    }

    public function form(IncomeCategoryFormRequest $request): ApiResponse {
        $response = new ApiResponse();
        $params = $request->validated();

        if(!empty($params['id']) && $request->isMethod(Request::METHOD_PUT)) {
            return $this->update($params);
        }
        else if(empty($params['id']) && $request->isMethod(Request::METHOD_POST)) {
            return $this->create($params);
        }
        else {
            $response->status = Response::HTTP_METHOD_NOT_ALLOWED;
            $response->message = 'Invalid method.';
            return $response;
        }
    }

    public function create(array $fieldSet): ApiResponse {
        $response = new ApiResponse();

        if(empty($fieldSet)) {
            $response->status = Response::HTTP_BAD_REQUEST;
            $response->message = 'Invalid arguments. Params must be provided.';
            return $response;
        }

        $id = $this->incomeCategoryService->create($fieldSet);

        if(!empty($id)) {
            $response->data = [
                'id' => $id
            ];
            $response->message = 'Income category created successfully.';
        }
        else {
            $response->status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response->message = 'An error occured while trying to create an income category entry.';
        }

        return $response;
    }

    public function update(array $params): ApiResponse {
        $response = new ApiResponse();

        if(empty($params)) {
            $response->status = Response::HTTP_BAD_REQUEST;
            $response->message = 'Invalid arguments. Params must be provided.';
            return $response;
        }

        $id = $this->incomeCategoryService->update($params);

        if(!empty($id)) {
            $response->data = [
                'id' => $id
            ];
            $response->message = 'Income category updated successfully.';
        }
        else {
            $response->status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response->message = 'An error occured while trying to update an income category entry.';
        }

        return $response;
    }

    public function delete(Request $request): ApiResponse {
        $response = new ApiResponse();
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'numeric']
        ]);

        if($validator->fails()) {
            $response->status = Response::HTTP_BAD_REQUEST;
            $response->message = 'Invalid arguments. Numeric id must be provided.';
            return $response;
        }
        
        $validatedData = $validator->validated();
        $isDeleted = $this->incomeCategoryService->delete($validatedData['id']);

        if(!empty($isDeleted)) {
            $response->message = 'Income category deleted successfully.';
        }
        else {
            $response->status = Response::HTTP_INTERNAL_SERVER_ERROR;
            $response->message = 'An error occured while trying to delete an income category entry.';
        }

        return $response;
    }
}
